<?php

namespace SF9\InventoryStockBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;
use SF9\InventoryStockBundle\Entity\Inventory;
use SF9\InventoryStockBundle\Repository\InventoryRepository;

use Exception;

/**
 * A console command that imports inventory stock information from a given CSV file.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console inventory:import-stock [<csv-path>]
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console inventory:import-stock -vv
 *
 */
class ImportStockCommand extends Command
{

    const BATCH_SIZE = 10000;

    protected static $defaultName = 'inventory:import-stock';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var InventoryRepository
     */
    private $inventoryRepository;

    /**
     * @var string
     */
    private $csvPath;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $csvPath, string $projectDir, InventoryRepository $ir)
    {

        parent::__construct();

        $this->csvPath = $csvPath;
        $this->projectDir = $projectDir;
        $this->inventoryRepository = $ir;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Imports inventory stock data from a given CSV')
            ->setHelp($this->getCommandHelp())
            ->addArgument('csv-path', InputArgument::OPTIONAL, 'Path to the csv file that contains the new inventory stock data. If non provided default path from config will be used.')
        ;
    }

    /**
     * Setting output decorator. Reading csv path from command arguments ,
     * if non provided falling back to csv path passed set in constructor
     * 
     * @param InputInterface $input command input, could containe csv file path
     * @param OutputInterface $output
     * 
     * @return void
     */

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        if (null !== ($csvPath = $input->getArgument('csv-path')) ) {
            $this->csvPath = $csvPath;
        }
    }

    /**
     * Opening the handler to csv file and sending the file to batch processing
     * 
     * Timeing the command run.
     * 
     * @param InputInterface $input not used
     * @param OutputInterface $output
     * 
     * @return int command status
     */

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('import-stock-command');

        $fullFilePath = $this->projectDir . '/' .$this->csvPath;

        if(!file_exists($fullFilePath))
        {
            $this->io->error('File: ' . $fullFilePath . ' not found!');
            return Command::FAILURE;
        }

        $this->io->info('Importing from file: ' . $fullFilePath);

        $csvFileHandler = fopen($fullFilePath, 'r');

        // grabbing the headers from beginning of the file and flipping them for the mapper
        $headers = array_flip(array_map('strtolower', fgetcsv($csvFileHandler)));

        $batchNum = 0;

        while(!feof($csvFileHandler))
        {
            $this->processBatch($csvFileHandler, $headers, $batchNum);

            $batchNum++;
        }

        $event = $stopwatch->stop('import-stock-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('Data imported / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return Command::SUCCESS;
    }

    /**
     * Processing data from csv file in batches, it was setup this way for performace
     * 
     * All the records in the csv file that are well formed, .ie have all the required columns
     * and are within the current batch are stored to permanent storage (database)
     * 
     * @param resource $csvFileHandler file handler to csv file
     * @param array $headers indexes of headers in csv file, 
     *              used to find in what columns is the required 
     *              data located (sku, branch, stock)
     * @param int $batchNum sequence number of the batch that is being processed
     * 
     * @return void
     */

    private function processBatch($csvFileHandler, array $headers, int $batchNum): void
    {
        $count = 0;
        $inventories = [];

        while(!feof($csvFileHandler) && $count !== self::BATCH_SIZE)
        {
            $data = fgetcsv($csvFileHandler);

            if(is_array($data)) {

                try {
                    $mappedInventoryArray = $this->getMappedInventoryArray($headers, $data);
                } catch(Exception $e) {
                    $this->io->error('Record row: ' . ($count + ($batchNum * self::BATCH_SIZE)) . ' -> ' . json_encode($data));
                    $this->io->error($e->getMessage());
                    $this->io->info('Skipping malformed record!');
                    continue;
                }

                $inventories[] = new Inventory($mappedInventoryArray);
            }
            $count++;
        }

        $this->inventoryRepository->persistMany($inventories);
    }

    /**
     * Transforming indexed array to associative array containing only the required columns
     * 
     * @param array $headers indexes of headers in csv file, 
     *              used to find in what columns is the required 
     *              data located (sku, branch, stock)
     * @param array $data row from the csv file as array
     * 
     * @return array associative array of required columns
     * 
     * @throws Exception a row doesn't have all the required columns filled
     */

    private function getMappedInventoryArray(array $headers, array $data): array
    {
        $mappedInventoryArray = [];

        foreach($headers as $headerColumnName => $headerIndex)
        {
            if(isset($data[$headerIndex])) {
                $mappedInventoryArray[$headerColumnName] = $data[$headerIndex];
            } else {
                throw new Exception('CSV record malformed!');
            }
        }

        return $mappedInventoryArray;
    }

    /**
     * The command help is usually included in the configure() method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
The <info>%command.name%</info> imports inventory stock data from a given csv file:

<info>php %command.full_name%</info> <comment>[csv-path]</comment>

The structure of the csv file <info>must</info> begin with a header line containing <comment>SKU,BRANCH,STOCK</comment>, order of the columns isn't important.
HELP;
    }
}

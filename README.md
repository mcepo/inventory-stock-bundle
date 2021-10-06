Inventory Stock Bundle
========================

The "Inventory Stock Bundle" imports product stock data and handles them.

Usage
-----

Register the bundle in your application, in **bundles.php**

```
...
SF9\InventoryStockBundle\InventoryStockBundle::class => ['all' => true],
...
```

Example of config in **config/packages/inventory_stock.yaml**
<br/>
```
inventory_stock:
    # csv path relative to web root
    csv_path: 'data/stock-file.csv'
    # mail information for out of stock notification
    out_of_stock_email_to: 'marko.cepo.vk@gmail.com'
    out_of_stock_email_from: 'no-replay@support.test'
```
<br/><br/>

Example of routes in **config/routes/inventory_stock.yaml**
make sure to add a prefix since package is using '/' for index
<br/>
```
inventory_stock:
    resource: '@InventoryStockBundle/Resources/config/routes.xml'
    prefix: /inventory_stock
```

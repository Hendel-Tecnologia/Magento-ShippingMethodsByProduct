# ShippingMethodsByProduct

The module add a new api endpoint to get resource (Product or Category) key url

## Install

In the magento root folder, run the following commands:

```sh
mkdir -p app/code/Hendel
```

```sh
git clone https://github.com/Hendel-Tecnologia/Magento-ShippingMethodsByProduct.git app/code/Hendel/ShippingMethodsByProduct
```

```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

## Usage

```
GET /V1/hendel/shipping/estimate/:sku
```

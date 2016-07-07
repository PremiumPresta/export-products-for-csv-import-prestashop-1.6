<?php

class AdminProductsController extends AdminProductsControllerCore
{

    /**
     * Default exported fields
     * ID, Image, Name, Reference, Category, Base price, Final price, Quantity, Status
     */
    public function processExport($text_delimiter = '"')
    {
        // Reference is Reference #
        $this->_select .= ', a.`reference`, ';
        $this->fields_list['reference']['title'] = 'Reference #';

        /* Columns with callback function */

        // Image URLs (x,y,z...)
        $this->fields_list['image']['title'] = 'Image URLs (x,y,z...)';
        $this->fields_list['image']['callback'] = 'exportAllImagesLink';

        // Categories (x,y,z...)
        $this->fields_list['name_category']['title'] = 'Categories (x,y,z...)';
        $this->fields_list['name_category']['callback'] = 'exportAllProductCategories';

        // Features (Name:Value:Position:Customized, ...)
        $this->_select .= 'NULL AS features, ';
        $this->fields_list['features'] = array(
            'title' => $this->l('Feature (Name:Value:Position:Customized)'),
            'callback' => 'exportFeatures'
        );

        // Tags (x,y,z...)
        $this->_select .= 'NULL AS tags, ';
        $this->fields_list['tags'] = array(
            'title' => $this->l('Tags (x,y,z...)'),
            'callback' => 'exportTags'
        );

        /* Fields with join */

        // Needed for supplier fields
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'product_supplier` product_supplier ON (a.`id_product` = product_supplier.`id_product` AND product_supplier.`id_product_attribute` = 0)';

        // Supplier
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'supplier` supplier ON (product_supplier.`id_supplier` = supplier.`id_supplier`)';
        $this->_select .= 'supplier.`name` AS supplier_name, ';
        $this->fields_list['supplier_name'] = array('title' => $this->l('Supplier'));

        // Supplier reference #
        $this->_select .= 'product_supplier.`product_supplier_reference` AS supplier_reference, ';
        $this->fields_list['supplier_reference'] = array('title' => $this->l('Supplier reference #'));

        // Manufacturer
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` manufacturer ON (a.`id_manufacturer` = manufacturer.`id_manufacturer`)';
        $this->_select .= 'manufacturer.`name` AS manufacturer_name, ';
        $this->fields_list['manufacturer_name'] = array('title' => $this->l('Manufacturer'));

        // Needed for Discount fields
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'specific_price` specific_price ON (a.`id_product` = specific_price.`id_product` AND specific_price.`id_product_attribute` = 0)';

        // Discount amount
        $this->_select .= '(CASE WHEN specific_price.`reduction_type` = "amount" THEN specific_price.`reduction` END) AS discount_amount, ';
        $this->fields_list['discount_amount'] = array('title' => $this->l('Discount amount'));

        // Discount percent
        $this->_select .= '(CASE WHEN specific_price.`reduction_type` = "percentage" THEN specific_price.`reduction` * 100 END) AS discount_percent, ';
        $this->fields_list['discount_percent'] = array('title' => $this->l('Discount percent'));

        // Discount from (yyyy-mm-dd)
        $this->_select .= 'IF(DATE(specific_price.`from`) = DATE("0000-00-00"), NULL, DATE(specific_price.`from`)) AS discount_from, ';
        $this->fields_list['discount_from'] = array('title' => $this->l('Discount from (yyyy-mm-dd)'));

        // Discount to (yyyy-mm-dd)
        $this->_select .= 'IF(DATE(specific_price.`to`) = DATE("0000-00-00"), NULL, DATE(specific_price.`to`)) AS discount_to, ';
        $this->fields_list['discount_to'] = array('title' => $this->l('Discount to (yyyy-mm-dd)'));

        // Product availability date
        $this->_select .= 'IF(DATE(a.`available_date`) = DATE("0000-00-00"), NULL, DATE(a.`available_date`)) AS available_date, ';
        $this->fields_list['available_date'] = array('title' => $this->l('Product availability date'));

        // Product creation date
        $this->_select .= 'IF(TIMEDIFF(a.`date_add`, "0000-00-00 00:00:00"), a.`date_add`, NULL) AS date_add, ';
        $this->fields_list['date_add'] = array('title' => $this->l('Product creation date'));

        // Depends on stock
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` stock_available ON (a.`id_product` = stock_available.`id_product` AND stock_available.`id_product_attribute` = 0)';
        $this->_select .= 'stock_available.`depends_on_stock`, ';
        $this->fields_list['depends_on_stock'] = array('title' => $this->l('Depends on stock'));

        // Warehouse id
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'warehouse_product_location` wpl ON (a.`id_product` = wpl.`id_product` AND wpl.`id_product_attribute` = 0)';
        $this->_select .= 'wpl.`id_warehouse`, ';
        $this->fields_list['id_warehouse'] = array('title' => $this->l('Warehouse'));

        /* Translatable fields */

        // Short description
        $this->_select .= 'b.`description_short` AS short_description, ';
        $this->fields_list['short_description'] = array('title' => $this->l('Short description'));

        // Description
        $this->_select .= 'b.`description`, ';
        $this->fields_list['description'] = array('title' => $this->l('Description'));

        // Meta title
        $this->_select .= 'b.`meta_title`, ';
        $this->fields_list['meta_title'] = array('title' => $this->l('Meta title'));

        // Meta keywords
        $this->_select .= 'b.`meta_keywords`, ';
        $this->fields_list['meta_keywords'] = array('title' => $this->l('Meta keywords'));

        // Meta description
        $this->_select .= 'b.`meta_description`, ';
        $this->fields_list['meta_description'] = array('title' => $this->l('Meta description'));

        // URL rewritten
        $this->_select .= 'b.`link_rewrite`, ';
        $this->fields_list['link_rewrite'] = array('title' => $this->l('URL rewritten'));

        // Text when in stock
        $this->_select .= 'b.`available_now`, ';
        $this->fields_list['available_now'] = array('title' => $this->l('Text when in stock'));

        // Text when backorder allowed
        $this->_select .= 'b.`available_later`, ';
        $this->fields_list['available_later'] = array('title' => $this->l('Text when backorder allowed'));

        /* Simple select */

        // Tax rules ID
        $this->_select .= 'a.`id_tax_rules_group`, ';
        $this->fields_list['id_tax_rules_group'] = array('title' => $this->l('Tax rules ID'));

        // Wholesale price
        $this->_select .= 'a.`wholesale_price`, ';
        $this->fields_list['wholesale_price'] = array('title' => $this->l('Wholesale price'));

        // On sale
        $this->_select .= 'a.`on_sale`, ';
        $this->fields_list['on_sale'] = array('title' => $this->l('On sale'));

        // EAN13
        $this->_select .= 'a.`ean13`, ';
        $this->fields_list['ean13'] = array('title' => $this->l('EAN13'));

        // UPC
        $this->_select .= 'a.`upc`, ';
        $this->fields_list['upc'] = array('title' => $this->l('UPC'));

        // Ecotax
        $this->_select .= 'a.`ecotax`, ';
        $this->fields_list['ecotax'] = array('title' => $this->l('Ecotax'));

        // Width
        $this->_select .= 'a.`width`, ';
        $this->fields_list['width'] = array('title' => $this->l('Width'));

        // Height
        $this->_select .= 'a.`height`, ';
        $this->fields_list['height'] = array('title' => $this->l('Height'));

        // Depth
        $this->_select .= 'a.`depth`, ';
        $this->fields_list['depth'] = array('title' => $this->l('Depth'));

        // Weight
        $this->_select .= 'a.`weight`, ';
        $this->fields_list['weight'] = array('title' => $this->l('Weight'));

        // Minimal quantity
        $this->_select .= 'a.`minimal_quantity`, ';
        $this->fields_list['minimal_quantity'] = array('title' => $this->l('Minimal quantity'));

        // Visibility
        $this->_select .= 'a.`visibility`, ';
        $this->fields_list['visibility'] = array('title' => $this->l('Visibility'));

        // Additional shipping cost
        $this->_select .= 'a.`additional_shipping_cost`, ';
        $this->fields_list['additional_shipping_cost'] = array('title' => $this->l('Additional shipping cost'));

        // Unit for the unit price
        $this->_select .= 'a.`unity`, ';
        $this->fields_list['unity'] = array('title' => $this->l('Unit for the unit price'));

        // Unit price
        $this->_select .= '(a.`price` / a.`unit_price_ratio`) AS unit_price, ';
        $this->fields_list['unit_price'] = array('title' => $this->l('Unit price'));

        // Available for order (0 = No, 1 = Yes)
        $this->_select .= 'a.`available_for_order`, ';
        $this->fields_list['available_for_order'] = array('title' => $this->l('Available for order (0 = No, 1 = Yes)'));

        // Show price (0 = No, 1 = Yes)
        $this->_select .= 'a.`show_price`, ';
        $this->fields_list['show_price'] = array('title' => $this->l('Show price (0 = No, 1 = Yes)'));

        // Available online only (0 = No, 1 = Yes)
        $this->_select .= 'a.`online_only`, ';
        $this->fields_list['online_only'] = array('title' => $this->l('Available online only (0 = No, 1 = Yes)'));

        // Condition
        $this->_select .= 'a.`condition`, ';
        $this->fields_list['condition'] = array('title' => $this->l('Condition'));

        // Customizable (0 = No, 1 = Yes)
        $this->_select .= 'a.`customizable`, ';
        $this->fields_list['customizable'] = array('title' => $this->l('Customizable (0 = No, 1 = Yes)'));

        // Uploadable files (0 = No, 1 = Yes)
        $this->_select .= 'a.`uploadable_files`, ';
        $this->fields_list['uploadable_files'] = array('title' => $this->l('Uploadable files (0 = No, 1 = Yes)'));

        // Text fields (0 = No, 1 = Yes)
        $this->_select .= 'a.`text_fields`, ';
        $this->fields_list['text_fields'] = array('title' => $this->l('Text fields (0 = No, 1 = Yes)'));

        // Action when out of stock (0 = Deny orders, 1 = Allow orders, 2 = Default)
        $this->_select .= 'a.`out_of_stock`, ';
        $this->fields_list['out_of_stock'] = array('title' => $this->l('Action when out of stock'));

        // ID / Name of shop
        $this->_select .= 'a.`id_shop_default`, ';
        $this->fields_list['id_shop_default'] = array('title' => $this->l('ID / Name of shop'));

        // Advanced Stock Management
        $this->_select .= 'a.`advanced_stock_management`, ';
        $this->fields_list['advanced_stock_management'] = array('title' => $this->l('Advanced Stock Management'));

        /* Predefined columns */

        // Delete existing images (0 = No, 1 = Yes)
        $this->_select .= '0 AS delete_images, ';
        $this->fields_list['delete_images'] = array('title' => $this->l('Delete existing images (0 = No, 1 = Yes)'));

        /* Rename fields */

        // Status is Active (0/1)
        $this->fields_list['active']['title'] = 'Active (0/1)';

        // Base price is Price tax excluded
        $this->fields_list['price']['title'] = 'Price tax excluded';

        // Final price is Price tax included
        $this->fields_list['price_final']['title'] = 'Price tax included';

        static::sortCSVfields($this->fields_list);

        parent::processExport($text_delimiter);
    }

    public static function exportAllImagesLink($cover, $row, $delimiter = ',')
    {
        if (empty($row) || empty($row['id_product']) || empty($row['id_image'])) {
            return;
        }

        $id_product = (int) $row['id_product'];
        $id_shop = Context::getContext()->shop->id;
        $links = array($cover); // the first link is the cover image

        $query = new DbQuery();
        $query->select('i.id_image')->from('image', 'i');
        $query->leftJoin('image_shop', 'is', 'i.id_image = is.id_image AND is.id_shop = ' . $id_shop);
        $query->where('i.id_product = ' . $id_product . ' AND (i.cover IS NULL OR i.cover = 0)');
        $images = Db::getInstance()->executeS($query);

        foreach ($images as $image) {
            if (Configuration::get('PS_LEGACY_IMAGES')) {
                $links[] = Tools::getShopDomain(true) . _THEME_PROD_DIR_ . $id_product . '-' . $image['id_image'] . '.jpg';
            } else {
                $links[] = Tools::getShopDomain(true) . _THEME_PROD_DIR_ . Image::getImgFolderStatic($image['id_image']) . $image['id_image'] . '.jpg';
            }
        }

        return implode($delimiter, $links);
    }

    public static function exportAllProductCategories($defaultCategory, $row, $delimiter = ',')
    {
        if (empty($row) || empty($row['id_product'])) {
            return;
        }

        $id_product = (int) $row['id_product'];
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();
        $query->select('cl.name')->from('category_lang', 'cl');
        $query->leftJoin('category_shop', 'cs', 'cl.id_category = cs.id_category AND cs.id_shop = ' . $id_shop);
        $query->leftJoin('category_product', 'cp', 'cl.id_category = cp.id_category AND cp.id_product = ' . $id_product);
        $query->leftJoin('product', 'p', 'cp.id_product = p.id_product');
        $query->where('cl.id_lang = ' . $id_lang . ' AND p.id_category_default != cl.id_category');

        $categories = array($defaultCategory); // the first category is the default one
        foreach (Db::getInstance()->executeS($query) as $category) {
            $categories[] = $category['name'];
        }

        return implode($delimiter, $categories);
    }

    public static function exportFeatures($feature, $row, $delimiter = ',')
    {
        if (empty($row) || empty($row['id_product'])) {
            return;
        }

        $id_product = (int) $row['id_product'];
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();
        $query->select('IF(LENGTH(feature_value_lang.value), CONCAT_WS(":", feature_lang.name, feature_value_lang.value, feature.position, feature_value.custom), NULL) AS feature')->from('feature', 'feature');
        $query->leftJoin('feature_lang', 'feature_lang', 'feature.id_feature = feature_lang.id_feature AND feature_lang.id_lang = ' . $id_lang);
        $query->leftJoin('feature_shop', 'feature_shop', 'feature.id_feature = feature_shop.id_feature AND feature_shop.id_shop = ' . $id_shop);
        $query->leftJoin('feature_product', 'feature_product', 'feature_product.id_feature = feature_product.id_feature AND feature_product.id_product = ' . $id_product);
        $query->leftJoin('feature_value', 'feature_value', 'feature.id_feature = feature_value.id_feature AND feature_product.id_feature_value = feature_value.id_feature_value');
        $query->leftJoin('feature_value_lang', 'feature_value_lang', 'feature_value.id_feature_value = feature_value_lang.id_feature_value AND feature_value_lang.id_lang = ' . $id_lang);

        $features = array();
        foreach (Db::getInstance()->executeS($query) as $feature) {
            if ($feature['feature']) {
                $features[] = $feature['feature'];
            }
        }

        return implode($delimiter, $features);
    }

    public static function exportTags($tag, $row, $delimiter = ',')
    {
        if (empty($row) || empty($row['id_product'])) {
            return;
        }

        $id_product = (int) $row['id_product'];
        $id_lang = Context::getContext()->language->id;

        $query = new DbQuery();
        $query->select('tag.name')->from('tag', 'tag');
        $query->innerJoin('product_tag', 'pt', 'tag.id_tag = pt.id_tag AND pt.id_product = ' . $id_product);
        $query->where('tag.id_lang = ' . $id_lang);

        $tags = array();
        foreach (Db::getInstance()->executeS($query) as $tag) {
            $tags[] = $tag['name'];
        }

        return implode($delimiter, $tags);
    }

    public static function sortCSVfields(&$fields)
    {
        ksort($fields);
        $positions = array(2, 27, 53, 40, 39, 38, 37, 47, 48, 41, 44, 54, 22, 31, 9, 11, 10, 12, 17, 19, 45, 21, 1, 52, 6, 55, 43, 36, 16, 35, 34, 33, 25, 3, 4, 8, 46, 51, 5, 56, 13, 24, 30, 42, 15, 14, 32, 50, 29, 28, 18, 49, 26, 23, 7, 20);
        array_multisort($positions, $fields);
    }

}

<?php
/**
 * Class Mage_Catalog_Block_Product_List_SortingMenu
 *
 * @author imilyukov
 */
class Mage_Catalog_Block_Product_List_SortingMenu extends Mage_Core_Block_Template
{
    /**
     * @var array $_sortingAttributes;
     */
    protected $_sortAttributes;

    /**
     * @var array $_filterAttributes;
     */
    protected $_filterAttributes;

    /**
     * Method Mage_Catalog_Block_Product_List_SortingMenu::setParentBlock()
     */
    public function setParentBlock(Mage_Core_Block_Abstract $parentBlock)
    {
        $productCollection = $parentBlock->getLoadedProductCollection();

        $request = $this->getRequest();

        $sortAttributes = $request->getParam('sort', array());
        foreach ($sortAttributes as $attribute => $direction) {

            if ( !empty($direction) ) {

                $productCollection->addAttributeToSort($attribute, $direction);
            } // if
        } // foreach

        $filterAttributes = $request->getParam('filter', array());
        foreach ($filterAttributes as $attribute => $value) {

            if ( !empty($value) ) {

                $productCollection->addAttributeToFilter($attribute, $value);
            } // if
        } // foreach

        parent::setParentBlock($parentBlock);
    }

    /**
     * Method Mage_Catalog_Block_Product_List_SortingMenu::addAttributeToSorting()
     *
     * @param string $type
     * @param string $default
     */
    public function addAttributeToSort($type = 'default', $default = 'sort by')
    {
        if ( !$this->_sortAttributes ) {

            $this->_sortAttributes = new Varien_Data_Collection();
        }

        $request = $this->getRequest();
        $sortAttributes = $request->getParam('sort', array());

        $optionCollection = new Varien_Data_Collection();
        $optionCollection->addItem(new Varien_Object(array(
            'label' => 'ascending ' . $type,
            'value' => 'asc',
            'selected' => array_key_exists($type, $sortAttributes) && 'asc' == $sortAttributes[$type],
        )));

        $optionCollection->addItem(new Varien_Object(array(
            'label' => 'descending ' . $type,
            'value' => 'desc',
            'selected' => array_key_exists($type, $sortAttributes) && 'desc' == $sortAttributes[$type],
        )));

        $attributeInfo = new Varien_Object(array(
            'name' => 'sort[' . $type . ']',
            'type' => $type,
            'default' => $default,
            'options' => $optionCollection,
        ));

        $this->_sortAttributes->addItem($attributeInfo);
    }

    /**
     * Method Mage_Catalog_Block_Product_List_SortingMenu::addAttributeToFilter()
     *
     * @param string $type
     * @param string $default
     */
    public function addAttributeToFilter($type = 'default', $default = 'filter by')
    {
        if ( !$this->_filterAttributes ) {

            $this->_filterAttributes = new Varien_Data_Collection();
        }

        $productModel = Mage::getModel('catalog/product');
        $attribute = $productModel->getResource()->getAttribute($type);
        $attributeOptions = $attribute->getSource()->getAllOptions(false);

        $request = $this->getRequest();
        $filterAttributes = $request->getParam('filter', array());

        $optionCollection = new Varien_Data_Collection();
        foreach($attributeOptions as $attributeOption) {

            $attributeOption = new Varien_Object($attributeOption);
            $selected = array_key_exists($type, $filterAttributes)
                && $attributeOption->getValue() == $filterAttributes[$type];
            $attributeOption->setSelected($selected);
            $optionCollection->addItem($attributeOption);
        } // foreach

        $attributeInfo = new Varien_Object(array(
            'name' => 'filter[' . $type . ']',
            'type' => $type,
            'default' => $default,
            'options' => $optionCollection,
        ));

        $this->_filterAttributes->addItem($attributeInfo);
    }

    /**
     * Method Mage_Catalog_Block_Product_List_SortingMenu::getSortingAttributes()
     * @return mixed
     */
    public function getSortAttributes()
    {
        return $this->_sortAttributes;
    }

    /**
     * Method Mage_Catalog_Block_Product_List_SortingMenu::getFilterAttributes()
     * @return mixed
     */
    public function getFilterAttributes()
    {
        return $this->_filterAttributes;
    }

    /**
     * Method Mage_Catalog_Block_Product_List_SortingMenu::getAllAttributes()
     * @return mixed
     */
    public function getAllAttributes()
    {
        $collection = new Varien_Data_Collection();

        $sortAttributes = $this->_sortAttributes;
        foreach ($sortAttributes as $attribute) {

            $collection->addItem($attribute);
        }

        $filterAttributes = $this->_filterAttributes;
        foreach ($filterAttributes as $attribute) {

            $collection->addItem($attribute);
        }

        return $collection;
    }
}
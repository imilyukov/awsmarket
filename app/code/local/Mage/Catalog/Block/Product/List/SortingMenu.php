<?php
/**
 * Class Mage_Catalog_Block_Product_List_SortingMenu
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

            $productCollection->addAttributeToSort($attribute, $direction);
        } // foreach

        $filterAttributes = $request->getParam('filter', array());
        foreach ($filterAttributes as $attribute => $value) {

            $productCollection->addAttributeToFilter($attribute, $value);
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

        $attributeInfo = new Varien_Object(array(
            'type' => $type,
            'default' => $default,
            'options' => array(
                array(
                    'label' => 'ascending ' . $type,
                    'value' => 'asc',
                ),
                array(
                    'label' => 'descending ' . $type,
                    'value' => 'desc',
                ),
            ),
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

        $attributeOptions = array(
            'label' => $default,
            'value' => 0,
        ) + $attributeOptions;
        $attributeInfo = new Varien_Object(array(
            'type' => $type,
            'default' => $default,
            'options' => $attributeOptions,
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
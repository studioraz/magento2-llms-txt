<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class SocialMediaLinksField extends AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('name', [
            'label' => __('Platform Name'),
            'style' => '',
            'class' => 'required-entry',
        ]);
        $this->addColumn('url', [
            'label' => __('URL'),
            'style' => '',
            'class' => 'required-entry validate-url',
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        // This method is called for each row in the array
        // No special rendering needed for text inputs
    }
}


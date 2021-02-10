<?php
/**
 * @package   Zehntech_PriceMatrix
 * @author    Zehntech
 *
 */

namespace Zehntech\PriceMatrix\Block\Adminhtml\Olsmatrix\Edit;


/**
 * Adminhtml Add New Row Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);

    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
            ]
        );

        $form->setHtmlIdPrefix('');
        if ($model->getEntityId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Row Data'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Ols Price Matrix'), 'class' => 'fieldset-wide']
            );
        }

        $fieldset->addField(
            'min_price',
            'text',
            [
                'name' => 'min_price',
                'label' => __('Min Price'),
                'id' => 'min_price',
                'title' => __('Min Price'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'max_price',
            'text',
            [
                'name' => 'max_price',
                'label' => __('Max Price'),
                'id' => 'max_price',
                'title' => __('Max Price'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'markup',
            'text',
            [
                'name' => 'markup',
                'label' => __('Markup'),
                'id' => 'markup',
                'title' => __('Markup'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );


        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
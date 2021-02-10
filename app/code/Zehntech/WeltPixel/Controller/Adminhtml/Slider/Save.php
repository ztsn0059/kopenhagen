<?php

namespace Zehntech\WeltPixel\Controller\Adminhtml\Slider;

use Magento\Framework\App\Filesystem\DirectoryList;
use WeltPixel\OwlCarouselSlider\Model\Slider;

/**
 * Save Slider action
 * @category WeltPixel
 * @package  WeltPixel_OwlCarouselSlider
 * @module   OwlCarouselSlider
 * @author   WeltPixel Developer
 */
class Save extends \WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Slider
{
    /**
     * Dispatch request
     *
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formPostValues = $this->getRequest()->getPostValue();

        if (isset($formPostValues['slider'])) {
            $_sliderData = $formPostValues['slider'];
            $sliderId = isset($_sliderData['id']) ? $_sliderData['id'] : null;

            $sliderModel = $this->_sliderFactory->create();

            $sliderModel->load($sliderId);

            //image uploader
            // $sliderImage = $this->getRequest()->getFiles('image');
            $sliderImage = $this->getRequest()->getFiles('slider');
            $sliderImage = $sliderImage['image'];
            $fileName = ($sliderImage && array_key_exists('name', $sliderImage)) ? $sliderImage['name'] : null;

            $sliderData = [];
            if ($sliderImage && $fileName) {
                try {

                    /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                    $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                        ->getDirectoryRead(DirectoryList::MEDIA);                

                    /** @var \Magento\Framework\ObjectManagerInterface $uploader */
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'slider[image]']
                    );

                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setFilesDispersion(true);
                    $uploader->setAllowRenameFiles(true);

                    /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapterFactory */

                    $result = $uploader->save(
                        $mediaDirectory
                            ->getAbsolutePath(\WeltPixel\OwlCarouselSlider\Model\Banner::OWLCAROUSELSLIDER_MEDIA_PATH)
                    );

                    $sliderData['image'] = \WeltPixel\OwlCarouselSlider\Model\Banner::OWLCAROUSELSLIDER_MEDIA_PATH
                        . $result['file'];

                } catch (\Exception $e) {
                    if ($e->getCode() == 0) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }
            } else {
                if (isset($_sliderData['image']) && isset($_sliderData['image']['value'])) {
                    if (isset($_sliderData['image']['delete'])) {
                        $_sliderData['image'] = null;
                //         $_sliderData['delete_image'] = true;
                    } elseif (isset($_sliderData['image']['value'])) {
                        $_sliderData['image'] = $_sliderData['image']['value'];
                    }
                }
            } 
            //image uploader
            $_sliderData = array_merge($_sliderData,$sliderData);
            $sliderModel->setData($_sliderData);
            
            try {
                $sliderModel->save();

                if (isset($formPostValues['slider_banner'])) {
                    $bannerGridSerializedInputData = $this->_jsHelper->decodeGridSerializedInput($formPostValues['slider_banner']);
                    $bannerIds = [];

                    $bannerOrders = [];
                    foreach ($bannerGridSerializedInputData as $key => $value) {
                        $bannerIds[] = $key;
                        $bannerOrders[] = $value['sort_order'];
                    }

                    $unSelecteds = $this->_bannerCollectionFactory
                        ->create()
                        ->addFieldToFilter('slider_id', $sliderModel->getId());
                    ;

                    if (count($bannerIds)) {
                        $unSelecteds->addFieldToFilter('id', ['nin' => $bannerIds]);
                    }

                    foreach ($unSelecteds as $banner) {
                        $banner->setSliderId(0)
                            ->setSortOrder(0)->save();
                    }

                    $selectBanner = $this->_bannerCollectionFactory
                        ->create()
                        ->addFieldToFilter('id', ['in' => $bannerIds]);

                    $i = -1;
                    foreach ($selectBanner as $banner) {
                        $banner->setSliderId($sliderModel->getId())
                            ->setSortOrder($bannerOrders[++$i])->save();
                    }
                }

                $this->messageManager->addSuccess(__('The slider has been saved.'));
                $this->_getSession()->setFormData(false);

                return $this->_getResultRedirect($resultRedirect, $sliderModel->getId());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the slider.'));
            }

            $this->_getSession()->setFormData($formPostValues);

            return $resultRedirect->setPath('*/*/edit', [static::PARAM_CRUD_ID => $sliderId]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}

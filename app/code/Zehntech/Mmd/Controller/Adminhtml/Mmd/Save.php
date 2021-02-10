<?php
/**
 * Zehntech_Mmd Interface. *
 * @category    Zehntech_Mmd *
 * @author  @SumitKumarNamdeo
 */

namespace Zehntech\Mmd\Controller\Adminhtml\Mmd;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Zehntech\Mmd\Model\MmdFileFactory
     */
    var $gridFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Zehntech\Mmd\Model\MmdFileFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Zehntech\Mmd\Model\MmdFileFactory $gridFactory,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem
    )
    {
        parent::__construct($context);
        $this->gridFactory = $gridFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->_redirect('mmdfile/mmd/addrow');
            return;
        }

        /*file uploading */

        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {

            try {

                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'file']);
                $uploaderFactory->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'xml', 'doc', 'txt']);
                /*
                $imageAdapter = $this->adapterFactory->create();
                $uploaderFactory->addValidateCallback('custom_image_upload', $imageAdapter, 'validateUploadFile');
                */
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('zehntech/mmd');
                $result = $uploaderFactory->save($destinationPath);

                if (!$result) {
                    throw new LocalizedException(
                        __('File cannot be saved to path: $1', $destinationPath)
                    );
                }

                if ($result['file']) {
                    $this->messageManager->addSuccess(__('File has been successfully uploaded'));
                }

                $imagePath = 'zehntech/mmd' . $result['file'];
                $data['file'] = $imagePath;
                $data['status'] = 1;

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        /*file uploading */
        try {
            $rowData = $this->gridFactory->create();
            $rowData->setData($data);

            if (isset($data['id'])) {
                $rowData->setEntityId($data['id']);
            }

            $rowData->save();

            $this->messageManager->addSuccess(__('file data has been successfully submitted.'));

        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('mmdfile/mmd/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Zehntech_Mmd::save');
    }
}
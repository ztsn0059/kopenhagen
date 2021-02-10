<?php

namespace Zehntech\FmePostEvent\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface;
use FME\Events\Model\EventFactory;
use FME\Events\Model\MediaFactory;
use Zehntech\FmePostEvent\Helper\EventMail;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
use FME\Events\Model\Media\ConfigEevent;

class Post extends \Magento\Framework\App\Action\Action
{

    protected $_pageFactory;
    protected $_messageManager;
    protected $_event;
    protected $_eventMailHelper;

    public function __construct(
        Context $context,
        EventFactory $event,
        MediaFactory $mediaFactory,
        RequestInterface $request,
        PageFactory $pageFactory,
        ManagerInterface $messageManager,
        EventMail $eventmailhelper,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        ConfigEevent $configEevent
    )
    {

        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->_messageManager = $messageManager;
        $this->_request = $request;
        $this->_event = $event;
        $this->_mediaFactory = $mediaFactory;
        $this->_eventMailHelper = $eventmailhelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->configEevent = $configEevent;
    }

    public function execute()
    {
        $eventData = $this->_request->getParams();

        if (!empty($latlng = $this->getLatLong($eventData['event_venue'], $eventData['country']))):

            $eventData['latitude'] = $latlng[0];
            $eventData['longitude'] = $latlng[1];

        endif;

        /*
         * Factory @FME\Events\Model\Event;
         * @object $eventOb
         */

        $eventOb = $this->_event->create();

        /*
         * Factory @FME\Events\Model\Media;
         * @object $mediaOb
         */
        $mediaOb = $this->_mediaFactory->create();

        try {

            $eventOb->setData($eventData);

            /*
             * upload image
             *  upload the image after user save the event
             */

            $uploader = $this->uploaderFactory->create(['fileId' => 'event_image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $imageAdapter = $this->adapterFactory->create();

            /*
             * start of validated image
             */

            $uploader->addValidateCallback('custom_image_upload', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $destinationPath = $mediaDirectory->getAbsolutePath('tmp/events/event/media');

            /*
             * uploading image
             * @return $result []
             */

            $result = $uploader->save($destinationPath);

            if (!$result) {
                throw new LocalizedException(
                    __('File cannot be saved to path: $1', $destinationPath)
                );
            }


            if ($eventOb->save()) {
                /*
                 * insert image just after event saved
                 */
                if ($eventOb->getId()):

                    $mediaOb->setData('event_id', $eventOb->getId());
                    $mediaOb->setData('file', $result['file']);
                    $mediaOb->save();

                endif;

                $this->_eventMailHelper->notify($eventData['contact_name'], $eventData['contact_email'], $eventData['event_name']);

                $this->_messageManager->addSuccessMessage(__('Event submitted successfully.'));


            } else {

                $this->_messageManager->addErrorMessage(__('some error occurred.'));
            }

        } catch (\Exception $e) {

            $this->_messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('events');
        return $resultRedirect;
    }


    function getLatLong($address, $region)
    {
        $address = str_replace(" ", "+", $address);
        $json = file_get_contents("https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=AIzaSyAgtq4QCQ-OJFa2BJwtySDF32EhYAOIurM&region=$region");
        $json = json_decode($json);

        $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};

        $latLong = [$lat, $long];

        return $latLong;
    }

}

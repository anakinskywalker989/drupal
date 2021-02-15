<?php

namespace Drupal\lfi_contact_support\Service;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Locale\CountryManager;
use Drupal\lfi_device_check\Service\DeviceCheckManager;

/**
 * Class ContactSupportValidate
 *
 * @package Drupal\lfi_contact_support\Service
 */
class ContactSupportValidate implements ContactSupportValidateInterface {

  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Device check manager.
   *
   * @var \Drupal\lfi_device_check\Service\DeviceCheckManager
   */
  protected $checkDevice;

  /**
   * The current database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\lfi_contact_support\Service\ContactSupportServiceInterface
   */
  protected $contactSupportService;

  /**
   * ContactSupportValidate constructor.
   *
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\lfi_device_check\Service\DeviceCheckManager $checkDevice
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\lfi_contact_support\Service\ContactSupportServiceInterface $contact_support_service
   */
  public function __construct(EmailValidatorInterface $email_validator, EntityTypeManagerInterface $entityTypeManager,
                              DeviceCheckManager $checkDevice, Connection $database, ContactSupportServiceInterface $contact_support_service) {
    $this->emailValidator = $email_validator;
    $this->entityTypeManager = $entityTypeManager;
    $this->checkDevice = $checkDevice;
    $this->database = $database;
    $this->contactSupportService = $contact_support_service;
  }

  /**
   * {@inheritDoc}
   */
  public function validateData($data, $language) {
    $errors = [];
    // Name validation.
    if(empty($data['name']) || (strlen($data['name']) > 50) || (preg_match('~[0-9]~', $data['name']))) {
      $errors[] = [
        'status' => '422',
        'title' => $this->contactSupportService->getTranslatedLabels('Empty od wrong name', $language),
        'detail' => $this->contactSupportService->getTranslatedLabels('Name is not correct.', $language),
        'validation' => [
          'name' => 'name',
          'text' => $this->contactSupportService->getTranslatedLabels('Name is not correct.', $language),
        ],
      ];
    }

    // Email validation.
    if(!$this->emailValidator->isValid($data['email'])){
      $errors[] = [
        'status' => '422',
        'title' => $this->contactSupportService->getTranslatedLabels('Wrong email', $language),
        'detail' => $this->contactSupportService->getTranslatedLabels('Email is not correct.', $language),
        'validation' => [
          'name' => 'email',
          'text' => $this->contactSupportService->getTranslatedLabels('Email is not correct.', $language),
        ],
      ];
    }

    // Phone validation. Not strog because it's not mandatory field.
    if (!empty($data['phone'])) {
      $phone = str_replace(['-', ' ', '/', '+'], '', $data['phone']);
      if (strlen($phone)>50 && !is_numeric($phone)) {
        $errors[] = [
          'status' => '422',
          'title' => $this->contactSupportService->getTranslatedLabels('Wrong phone number', $language),
          'detail' => $this->contactSupportService->getTranslatedLabels('Phone number is not correct.', $language),
          'validation' => [
            'name' => 'phone',
            'text' => $this->contactSupportService->getTranslatedLabels('Phone number is not correct.', $language),
          ],
        ];
      }
    }

    // Country validation.
    $countries = CountryManager::getStandardList();
    $country_code = $data['country'];
    if (!isset($countries[$country_code])) {
      $errors[] = [
        'status' => '422',
        'title' => $this->contactSupportService->getTranslatedLabels('Unspecified country', $language),
        'detail' => $this->contactSupportService->getTranslatedLabels('Country is not on list.', $language),
        'validation' => [
          'name' => 'country',
          'text' => $this->contactSupportService->getTranslatedLabels('Unspecified country.', $language),
        ],
      ];
    }

    // Check if category exist.
    $category_id = $data['categoryID'];
    if (!$this->entityTypeManager->getStorage('taxonomy_term')->load($category_id)) {
      $errors[] = [
        'status' => '422',
        'title' => $this->contactSupportService->getTranslatedLabels('Category select error.', $language),
        'detail' => $this->contactSupportService->getTranslatedLabels('Unrecognized category.', $language),
        'validation' => [
          'name' => 'categoryID',
          'text' => $this->contactSupportService->getTranslatedLabels('Unrecognized category.', $language),
        ],
      ];
    }

    // Check if subject exist.
    $subject_id = $data['subjectID'];
    if (!$this->entityTypeManager->getStorage('contact_questions')->load($subject_id)) {
      $errors[] = [
        'status' => '422',
        'title' => $this->contactSupportService->getTranslatedLabels('Subject select error.', $language),
        'detail' => $this->contactSupportService->getTranslatedLabels('Unrecognized subject.', $language),
        'validation' => [
          'name' => 'subjectID',
          'text' => $this->contactSupportService->getTranslatedLabels('Unrecognized subject.', $language),
        ],
      ];
    }

    // Check additional field.
    $subject = $this->entityTypeManager->getStorage('contact_questions')->load($subject_id);
    if ($additional_field = $subject->getFieldType()) {
      $cardinality = $subject->getCardinality();
      for ($i=0; $i<$cardinality; $i++) {
        switch ($additional_field) {
          case 'text_field':
            $text_field = $data['fields']['text_field_'.$i];
            if (empty($text_field) && $subject->getMandatory()) {
              $errors[] = [
                'status' => '422',
                'title' => $this->contactSupportService->getTranslatedLabels('Empty text.', $language),
                'detail' => $this->contactSupportService->getTranslatedLabels('Empty mandatory text field.', $language),
                'validation' => [
                  'name' => 'text_field_'.$i,
                  'text' => $this->contactSupportService->getTranslatedLabels('Empty mandatory text field.', $language),
                ],
              ];
            } else {
              // Check is it sc or sn.
              $device = $this->checkDevice->getDeviceInfo($text_field);
              if (!empty($device['errors'])) {
                // Check is it order id or coupon.
                if ((!$order = $this->entityTypeManager->getStorage('commerce_order')->load($text_field)) &&
                  (!$coupon = $this->entityTypeManager->getStorage('commerce_promotion_coupon')->loadByProperties(['code' => $text_field]))){
                    $errors[] = [
                      'status' => '422',
                      'title' => $this->contactSupportService->getTranslatedLabels('Wrong code', $language),
                      'detail' => $this->contactSupportService->getTranslatedLabels('Code is not valid', $language),
                      'validation' => [
                        'name' => 'text_field_' . $i,
                        'text' => $this->contactSupportService->getTranslatedLabels('Code is not valid.', $language),
                      ],
                    ];
                  }
                }
              }
            break;
          case 'product_reference':
            if (!empty($data['productID'])) {
              $product_id = $data['productID'];
              $query = $this->database->select('commerce_product')
                ->fields('commerce_product', ['product_id'])
                ->condition('product_id', $product_id, '=')
                ->execute()->fetchAll();
              if (!$query) {
                $errors[] = [
                  'status' => '422',
                  'title' => $this->contactSupportService->getTranslatedLabels('Product select error', $language),
                  'detail' => $this->contactSupportService->getTranslatedLabels('Unrecognized product.', $language),
                  'validation' => [
                    'name' => 'productID',
                    'text' => $this->contactSupportService->getTranslatedLabels('Unrecognized product.', $language),
                  ],
                ];
              }
            }
            break;
          case 'file_upload':
            if (empty($data['fields']['file_upload_'.$i])) {
              if ($subject->getMandatory()) {
                $errors[] = [
                  'status' => '422',
                  'title' => $this->contactSupportService->getTranslatedLabels('Missing image', $language),
                  'detail' => $this->contactSupportService->getTranslatedLabels('Empty mandatory file field.', $language),
                  'validation' => [
                    'name' => 'file_upload_'.$i,
                    'text' => $this->contactSupportService->getTranslatedLabels('Empty mandatory file field.', $language),
                  ],
                ];
              }
            }
            else {
              // Check extension of file.
              $file = $data['fields']['file_upload_'.$i];
              $lenght = strlen($file);
              $extension = substr($file, $lenght-4);
              if ((!strpos($extension, 'jpg')) && (!strpos($extension, 'jpeg')) && (!strpos($extension, 'png'))) {
                $errors[] = [
                  'status' => '422',
                  'title' => $this->contactSupportService->getTranslatedLabels('Wrong file.', $language),
                  'detail' => $this->contactSupportService->getTranslatedLabels('File must be image.', $language),
                  'validation' => [
                    'name' => 'file_upload_'.$i,
                    'text' => $this->contactSupportService->getTranslatedLabels('File must be image.', $language),
                  ],
                ];
              }
            }
            break;
        }
      }
    }

    return $errors;
  }

}

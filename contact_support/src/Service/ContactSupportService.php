<?php

namespace Drupal\lfi_contact_support\Service;

use Aws\S3\S3Client;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\Translator\TranslatorInterface;
use Drupal\lfi_device_check\Service\DeviceCheckManager;
use Drupal\lfi_zendesk\Service\ZendeskInterface;
use Drupal\s3fs\StreamWrapper\S3fsStream;
use GuzzleHttp\ClientInterface;

/**
 * Class ContactSupportService
 *
 * @package Drupal\lfi_contact_support\Service
 */
class ContactSupportService implements ContactSupportServiceInterface {

  use StringTranslationTrait;

  /**
   * Zendesk Api URL.
   */
  const API_URL = 'https://intimina.zendesk.com/api/v2/tickets.json';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CommerceGuys\Addressing\Country\CountryRepositoryInterface definition.
   *
   * @var \CommerceGuys\Addressing\Country\CountryRepositoryInterface
   */
  protected $countryRepository;

  /**
   * Zendesk service from lfi_zendesk.
   *
   * @var \Drupal\lfi_zendesk\Service\ZendeskInterface
   */
  protected $zendesk;

  /**
   * HTTP client service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * @var FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The translation manager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslatorInterface
   */
  protected $translationManager;

  /**
   * Device check manager.
   *
   * @var \Drupal\lfi_device_check\Service\DeviceCheckManager
   */
  protected $checkDevice;

  /**
   * ContactSupportService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \CommerceGuys\Addressing\Country\CountryRepositoryInterface $country_repository
   * @param \Drupal\lfi_zendesk\Service\ZendeskInterface $zendesk
   * @param \GuzzleHttp\ClientInterface $http_client
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   * @param \Drupal\lfi_device_check\Service\DeviceCheckManager $checkDevice
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, CountryRepositoryInterface $country_repository, ZendeskInterface $zendesk,
                              ClientInterface $http_client, FileSystemInterface $file_system, TranslatorInterface $translation, DeviceCheckManager $checkDevice) {
    $this->entityTypeManager = $entityTypeManager;
    $this->countryRepository = $country_repository;
    $this->zendesk = $zendesk;
    $this->httpClient = $http_client;
    $this->fileSystem = $file_system;
    $this->translationManager = $translation;
    $this->checkDevice = $checkDevice;
  }

  /**
   * {@inheritDoc}
   */
  public function getSubjects($language, $category) {
    $subject_data = [];
    $fields = [];
    $sort_subjects = [];
    $subject_storage = $this->entityTypeManager->getStorage('contact_questions');
    $subjects = $subject_storage->loadByProperties(['category' => $category]);
    /**
     * @var \Drupal\lfi_contact_support\Entity\ContactQuestions $subject
     */
    foreach ($subjects as $subject) {
      $sort_subjects[$subject->getWeight()] = $subject->id();
    }
    // Sort all subjects by weight.
    ksort($sort_subjects);

    foreach ($sort_subjects as $weight => $subject_id) {
      //Load sorted subject.
      $subject = $subject_storage->load($subject_id);
      // Get current user lng subject.
      $subject = $subject->hasTranslation($language) ? $subject->getTranslation($language) : $subject;
      // Prepare field type and cardinality.
      if ($subject->getFieldType()) {
        $fields = [];
        $cardinailty = (int)$subject->getCardinality();
        for ($i = 0; $i < $cardinailty; ++$i) {
          $fields[$i] = [
            'type' => $subject->getFieldType(),
            'mandatory' => $subject->getMandatory() ? TRUE : FALSE,
          ];
        }
      }
      $description = PlainTextOutput::renderFromHtml($subject->getDescription());
      $description = str_replace(["\r\n", "\r", "\n"], '', $description);
      $description = text_summary($description, 'filtered_format');
      $subject_data[] = [
        'id' => $subject->id(),
        'question' => $subject->getName(),
        'fields' => $fields,
        'description' => $description,
      ];
    }

    return $subject_data;
  }

  /**
   * {@inheritDoc}
   */
  public function getCategories($language) {
    $term_data = [];

    // Get current user lng category.
    // Retrieve the translated taxonomy term in specified language.
    $vid = 'contact_categories';
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid]);

    /**
     * @var \Drupal\taxonomy\Entity\Term $term
     */
    foreach ($terms as $term) {
      if ($term->hasTranslation($language)) {
        $term = $term->getTranslation($language);
      }

      $term_data[] = [
        'id' => $term->tid->value,
        'name' => $term->name->value,
      ];
    }

    return $term_data;
  }

  /**
   * {@inheritDoc}
   */
  public function getProducts($language) {
    $products = $this->entityTypeManager->getStorage('commerce_product')->loadMultiple();
    /**
     * @var \Drupal\commerce_product\Entity\Product $product
     */
    foreach ($products as $product) {
      // Get current user lng product.
      $product = $product->hasTranslation($language) ? $product->getTranslation($language) : $product;
      $product_data[] = [
        'id' => $product->id(),
        'name' => $product->getTitle(),
      ];
    }

    return $product_data;
  }

  /**
   * {@inheritDoc}
   */
  public function ccTeam($user_country) {
    $teams = $this->entityTypeManager->getStorage('mail_country_mapping')->loadMultiple();
    // Search for proper CC team.
    foreach ($teams as $team) {
      $countries = $team->getCountries();
      foreach ($countries as $country) {
        if ($user_country == $country) {
          return $team->getEmail();
        }
      }

    }
    // If search wasn't succesfull return $row.
    return 'customercare-row@foreo.com';
  }

  /**
   * {@inheritDoc}
   */
  public function prepareZendeskData($data) {
    $zendeskData = [];
    $text = '<br>' . 'Name: '. $data['name'] . '</br>'.'<br>'.'Message: '.$data['user_message'] . '</br>';
    // Get CC team mail.
    $ccTeam = $this->ccTeam($data['country']);

    // Mail message.
    $subject_id = $data['subjectID'];
    // @var \Drupal\lfi_contact_support\Entity\ContactQuestions $subject
    $subject = $this->entityTypeManager->getStorage('contact_questions')->load($subject_id);
    $subject_name = $subject->getName();

    if ($additional_field = $subject->getFieldType()) {
      $additional_field = $subject->getFieldType();
      $cardinality = $subject->getCardinality();
      for ($i=0; $i<$cardinality; $i++) {
        // Check what addional field is used and set it as body message.
        switch ($additional_field) {
          case 'text_field':
            $text_field = $data['fields']['text_field_' . $i];
            if (!empty($text_field)) {
              $device = $this->checkDevice->getDeviceInfo($text_field);
              if (!empty($device['errors'])) {
                // Check is it order id.
                if (!$order = $this->entityTypeManager->getStorage('commerce_order')->load($text_field)) {
                  $text .= '<br>' .$text_field . '</br>';
                } else {
                  $text .= '<br>'.'Order ID: ' .$text_field . '</br>';
                }
              }  else {
                $text .= '<br>'.'Product ID / Scratchcard code: ' .$text_field . '</br>';
              }
            }
            // In subject title set only first text_field.
            if ($i === 0) {
              $subject_name .= '  ' . $data['fields']['text_field_0'];
            }
            break;
          case 'product_reference':
            $product = $this->entityTypeManager->getStorage('commerce_product')
              ->load($data['productID']);
            $text .= '<br>' . 'Product: ' .$product->getTitle() .'</br>';
            break;
          case 'file_upload':
            $file = $data['fields']['file_upload_' . $i];
            $filename = substr($file, strpos($file, '.com/'));
            $filename = substr($filename, 5);
            // upload file to zendesk.
            $text .= '<br>'.'Image upload: ' . $filename .'</<br>>';
            $response = $this->uploadImageToZendesk($filename);
            $token[$i] = $response['upload']['token'];
            $zendeskData['ticket']['comment']['uploads'][] = $token[$i];
            break;
          case 'text_area':
            $text_area = $data['fields']['text_area_' . $i];
            $text .= $text_area;
            break;
        }
      }
    }
    // Set required Zendesk data.
    // Add first text field to subject title.
    $zendeskData['ticket']['subject'] = $subject_name;
    $zendeskData['ticket']['comment']['html_body'] = $text;
    $zendeskData['ticket']['type'] = $subject->getSubjectType();
    $zendeskData['ticket']['priority'] = $subject->getPriority();
    $zendeskData['ticket']['status'] = 'new';
    $zendeskData['ticket']['recipient'] = $ccTeam;
    $zendeskData['ticket']['requester'] = $data['email'];

    // Zendesk 'related to' field.
    $tid = $data['categoryID'];
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    $term = $term->name->value;
    $termCheck = strtolower($term);
    if (($termCheck === 'products') || ($termCheck === 'product') ||
      ($termCheck === 'warranty claims') || ($termCheck === 'warranty claim')) {
      $relatedTo['value'] = 'product';
    } elseif (($termCheck === 'orders') || ($termCheck === 'order')) {
      $relatedTo['value'] = 'order';
    } else {
      $relatedTo['value'] = 'none';
    }
    $relatedTo['id'] = 49571808;
    $zendeskData['ticket']['custom_fields'][] = $relatedTo;

    // Zendesk 'matching subject' field.
    $matchingSubject['id'] = 56864768;
    $matchingSubject['value'] = $subject->getMatchingSubject();
    $zendeskData['ticket']['custom_fields'][] = $matchingSubject;

    // Zendesk 'product id' field.
    $productId['id'] = 56737567;
    if (($relatedTo === 'product') && ($additional_field === 'text_field')) {
      $productId['values'] = $text_field = $data['fields']['text_field_0'];
    }

    // Set Zendesk user country field on eng lang.
    $country_code = $data['country'];
    $country = $this->countryRepository->get($country_code, 'en');
    $country = strtolower($country->getName());
    $country = str_replace(' ', '_', $country);
    $zendeskCountry['id'] = 360011003154;
    $zendeskCountry['value'] = $country;
    $zendeskData['ticket']['custom_fields'][] = $zendeskCountry;

    return $zendeskData;
  }

  /**
   * {@inheritDoc}
   */
  public function createZendeskTicket($data) {
    $data = Json::encode($data);
    $url = self::API_URL;
    // User and password token.
    $zendesk_userpwd = $this->zendesk->getApiUser() . "/token:" . $this->zendesk->getApiKey();
    $auth = base64_encode($zendesk_userpwd);

    // Prepare request.
    $response = $this->httpClient->request('POST', $url, ['headers' => [
      'Authorization' => 'Basic ' . $auth,
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
    ], 'body' => $data, 'verify' => FALSE]);

    return $response;
  }

  /**
   * {@inheritDoc}
   */
  public function uploadImageToZendesk($filename) {
    // Download image.
    $image = $this->getAwsImage($filename);

    $url = 'https://intimina.zendesk.com/api/v2/uploads.json?filename=' . $filename;
    // User and password token.
    $zendesk_userpwd = $this->zendesk->getApiUser() . "/token:" . $this->zendesk->getApiKey();

    $headers = [
      'Authorization: Basic ' . $zendesk_userpwd,
      'Content-Type: application/binary',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($image));

    // Execute and get result AS JSON, if any returned.
    $response = curl_exec($ch);

    // Get any CURL error occurred.
    $curl_error = curl_errno($ch);
    if (!empty($curl_error)) {
      return [];
    }

    // Close handle.
    curl_close($ch);

    // Delete image from temp storage.
    $this->fileSystem->delete($image);

    $response = Json::decode($response);

    // TODO curl_info response.
    return $response;
  }

  /**
   * {@inheritDoc}
   */
  public function deleteAwsImage($data) {
    // Set aws credentials.
    $bucket = 'contactform-zendesk-foreo';
    $client_config = $this->getAwsConf();
    // Create the Aws\S3\S3Client object.
    $s3 = new S3Client($client_config);

    // Load subject.
    $subject_id = $data['subjectID'];
    $subject = $this->entityTypeManager->getStorage('contact_questions')->load($subject_id);
    $cardinality = $subject->getCardinality();
    foreach ($cardinality as $i => $key) {
      $file = $data['fields']['file_upload_' . $i];
      $filename = substr($file, strpos($file, '.com/'));
      $filename = substr($filename, 5);
      // Delete image.
      $delete = $s3->deleteObject([
        'Bucket' => $bucket,
        'Key' => $filename,
      ]);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getAwsImage($filename) {
    // Set aws credentials.
    $bucket = 'contactform-zendesk-foreo';
    $client_config = $this->getAwsConf();
    // Create the Aws\S3\S3Client object.
    $s3 = new S3Client($client_config);

    $image = $s3->getObject([
      'Bucket' => $bucket,
      'Key' => $filename,
      'SaveAs' => $filename,
    ]);

    if ($image) {
      return $filename;
    }
  }

  /**
   * @param $string
   * @param $language
   *
   * @return false|string
   */
  public function getTranslatedLabels($string, $language) {
    // Translate hardcoded title.
    return $this->translationManager->getStringTranslation($language, $string, '')
      ? $this->translationManager->getStringTranslation($language, $string, '') : $string;
  }

  /**
   * Get AWS credentials.
   *
   * @return mixed
   */
  private function getAwsConf() {
    // Set aws credentials.
    $access_key = Settings::get('contact_s3fs_key');
    $access_secret = Settings::get('contact_s3fs_secret');
    $client_config['credentials'] = [
      'key' => $access_key,
      'secret' => $access_secret,
    ];
    $client_config['region'] = 'us-west-2';
    $client_config['version'] = S3fsStream::API_VERSION;

    return $client_config;
  }

}

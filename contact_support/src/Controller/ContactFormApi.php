<?php

namespace Drupal\lfi_contact_support\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lfi_contact_support\Service\ContactSupportServiceInterface;
use Drupal\lfi_contact_support\Service\ContactSupportValidateInterface;
use Drupal\lfi_geo\LfiGeoLocation;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContactFormApi
 *
 * @package Drupal\lfi_contact_support\Controller
 */
class ContactFormApi implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var string
   */
  protected $method;

  /**
   * @var array
   */
  protected $data;

  /**
   * @var \Drupal\Core\Session\AccountProxy $currentUser
   */
  protected $currentUser;

  /**
   * @var \Drupal\lfi_contact_support\Service\ContactSupportServiceInterface
   */
  protected $contactSupportService;

  /**
   * @var \Drupal\lfi_contact_support\Service\ContactSupportValidateInterface
   */
  protected $contactSupportValidate;

  /**
   * ContactFormApi constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   * @param \Drupal\lfi_contact_support\Service\ContactSupportServiceInterface $contact_support_service
   * @param \Drupal\lfi_contact_support\Service\ContactSupportValidateInterface $contact_support_validate
   */
  public function __construct(Request $request, LanguageManagerInterface $languageManager, AccountProxy $currentUser,
                              ContactSupportServiceInterface $contact_support_service, ContactSupportValidateInterface $contact_support_validate) {
    $this->request = $request;
    $this->languageManager = $languageManager;
    $this->currentUser = $currentUser;
    $this->contactSupportService = $contact_support_service;
    $this->contactSupportValidate = $contact_support_validate;

    // Request driven params.
    $this->method = $request->getMethod();
    $this->language = $request->headers->get('Accept-Language') ?? 'en';
    $this->data = [];
    // For post make validation.
    if ($this->method !== 'GET') {
      $this->data = $this->validation();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('language_manager'),
      $container->get('current_user'),
      $container->get('lfi_contact_support.data'),
      $container->get('lfi_contact_support.validate')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function initRequest() {
    // Check specific per route available methods.
    if (!empty($this->allowedMethods)){
      if (!in_array($this->method, $this->allowedMethods, TRUE)) {
        $errors[] = [
          'status' => '422',
          'title' => 'Wrong method',
          'detail' => 'You are trying to use method which is not allowed',
        ];
      }
    }

    // Check if validation have errors, if yes,
    // append them.
    if (isset($this->data['errors'])) {
      $errors = array_merge($errors, $this->data['errors']);
    }

    // If we have errors return error response.
    if (!empty($errors)) {
      return $this->fail($errors);
    }

    // Continue to router by method type.
    return $this->{strtolower($this->method)}();
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    $data = [];
    $data['name'] = '';
    $data['email'] = '';
    $data['phone'] = '';
    $data['country'] = '';
    $data['user_country'] = LfiGeoLocation::countryCode();

    // Get language.
    $language = $this->language;

    // Set labels on user lang. Default is eng.
    $data['form_label'] = $this->contactSupportService->getTranslatedLabels('Get in touch', $language);
    $data['form_description'] = $this->contactSupportService->getTranslatedLabels('We would love to hear from you! If you have any questions or comments that you\'d like to share with us, please fill out the contact form below, or feel free to give us a call at the phone number listed for your region.', $language);
    $data['name_label'] = $this->contactSupportService->getTranslatedLabels('Your full name', $language);
    $data['email_label'] = $this->contactSupportService->getTranslatedLabels('Your email address', $language);
    $data['phone_label'] =  $this->contactSupportService->getTranslatedLabels('Your phone number', $language);
    $data['country_label'] = $this->contactSupportService->getTranslatedLabels('Country', $language);
    $data['category_label'] = $this->contactSupportService->getTranslatedLabels('Select category', $language);
    $data['subject_label'] = $this->contactSupportService->getTranslatedLabels('Select subject', $language);
    $data['message_label'] = $this->contactSupportService->getTranslatedLabels('Input text', $language);
    $data['user_message_label'] = $this->contactSupportService->getTranslatedLabels('Your message', $language);

    $countries = CountryManager::getStandardList();
    $data['country'] = $countries;

    // Get user.
    $user = User::load($this->currentUser->id());

    // Load authenticated user data.
    if ($this->currentUser->isAuthenticated()) {
      $profile = '';
      if ($user!==NULL) {
        $first = $user->get('field_first_name')->value;
        $last = $user->get('field_last_name')->value;
        if (!empty($first)) {
          $profile .= $first;
        }
        $profile .= ' ';
        if (!empty($last)) {
          $profile .= $last;
        }
        $profile = trim($profile);
      }
      /**
       * @var \Drupal\phone_number\Element\PhoneNumber $phone
       */
      $phone = $user->get('phone_number')->value;
      $data['name'] = $profile;
      $data['email'] = $user->getEmail();
      $data['phone'] = $phone;
    }

    $products = $this->contactSupportService->getProducts($language);
    $data['products'] = $products;

    $categories = $this->contactSupportService->getCategories($language);
    // Set subjects under category object.
    foreach ($categories as $key => $category) {
      $subjects = $this->contactSupportService->getSubjects($language, $category);
      $data['category'][$key] = $category;
      $data['category'][$key]['subjects'] = $subjects;
    }

    // Add cache context.
    $cache_metadata = new CacheableMetadata();
    $cache_metadata->addCacheContexts(['route', 'lfi_api_lang', 'user', 'session']);

    // Make request.
    $json_response = new CacheableJsonResponse($data);
    $json_response->addCacheableDependency($cache_metadata);

    // Return user object.
    return $json_response;
  }

  /**
   * {@inheritDoc}
   */
  public function post() {
    $validations = [];
    if ($this->method == 'POST') {
      $content = $this->request->getContent();
      $data = Json::decode($content);
      $errors = $this->contactSupportValidate->validateData($data, $this->language);
      if (!empty($errors)) {
        foreach ($errors as $error) {
          $validation = [$error['validation']['name'] => $error['validation']['text']];
          $validations = $validations + $validation;
        }
        return new JsonResponse(['errors' => $errors, 'validation' => $validations], 422);
      }

      // Prepare data and send it to zendesk.
      $zendeskData = $this->contactSupportService->prepareZendeskData($data);
      $response = $this->contactSupportService->createZendeskTicket($zendeskData);

      // Delete used image from AWS bucket.
      if (!empty($data['fields']['file_upload_0'])){
        $this->contactSupportService->deleteAwsImage($data);
      }

      if ($response){
        return new JsonResponse(['success' => TRUE],200);
      }
      return new JsonResponse(['error' => 'Something went wrong']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fail($messages = []) {
    // If we have errors, but no messages return default.
    if (empty($messages)) {
      $messages[] = ['status' => '422', 'title' => 'Unprocessable Entity', 'detail' => 'Unprocessable Entity'];
    }

    // Return json response.
    return new JsonResponse(['errors' => $messages], 422);
  }

  /**
   * Validate post and patch input content.
   *
   * @return array
   *   Return array of data or send failed response
   */
  protected function validation() {
    $content = $this->request->getContent();
    $parameters = Json::decode($content);

    // Run this before validator, we want sanitized data.
    // Even if someone send without data key, it would be error called from
    // validator.
    if (isset($parameters['data'])) {
      foreach ($parameters['data'] as $key => $subdata) {
        // If is array, loop.
        if (is_array($subdata)) {
          foreach ($subdata as $subkey => $attributes) {
            // Check for depth.
            if(is_array($attributes)){
              foreach ($attributes as $attr_key => $attribute){
                $parameters['data'][$key][$subkey][$attr_key] = Xss::filter(trim($attribute), []);
              }
            }else{
              $parameters['data'][$key][$subkey] = Xss::filter(trim($attributes), []);
            }
          }
        }

        // Just sanitize
        else {
          $parameters['data'][$key] = Xss::filter(trim($subdata), []);
        }
      }
    }

    return $parameters;
  }

}

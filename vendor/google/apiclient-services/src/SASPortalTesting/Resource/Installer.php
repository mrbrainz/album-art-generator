<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\SASPortalTesting\Resource;

use Google\Service\SASPortalTesting\SasPortalGenerateSecretRequest;
use Google\Service\SASPortalTesting\SasPortalGenerateSecretResponse;
use Google\Service\SASPortalTesting\SasPortalValidateInstallerRequest;
use Google\Service\SASPortalTesting\SasPortalValidateInstallerResponse;

/**
 * The "installer" collection of methods.
 * Typical usage is:
 *  <code>
 *   $prod_tt_sasportalService = new Google\Service\SASPortalTesting(...);
 *   $installer = $prod_tt_sasportalService->installer;
 *  </code>
 */
class Installer extends \Google\Service\Resource
{
  /**
   * Generates a secret to be used with the ValidateInstaller.
   * (installer.generateSecret)
   *
   * @param SasPortalGenerateSecretRequest $postBody
   * @param array $optParams Optional parameters.
   * @return SasPortalGenerateSecretResponse
   */
  public function generateSecret(SasPortalGenerateSecretRequest $postBody, $optParams = [])
  {
    $params = ['postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('generateSecret', [$params], SasPortalGenerateSecretResponse::class);
  }
  /**
   * Validates the identity of a Certified Professional Installer (CPI).
   * (installer.validate)
   *
   * @param SasPortalValidateInstallerRequest $postBody
   * @param array $optParams Optional parameters.
   * @return SasPortalValidateInstallerResponse
   */
  public function validate(SasPortalValidateInstallerRequest $postBody, $optParams = [])
  {
    $params = ['postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('validate', [$params], SasPortalValidateInstallerResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Installer::class, 'Google_Service_SASPortalTesting_Resource_Installer');

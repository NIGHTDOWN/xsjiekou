<?php
/**
 * Copyright (c) 2014-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

// Configurations
$access_token = 'EAAJIKwgRIg8BAJitK7kTZC6UHwQXDANsZCoMjKdURI1fZCG8EFozYL63av6xjoVQZBuQgDUQTj87QXevBTXZCvaE7XeTqprAkmqc2596SEM48uMalbAiyX1HB9r6byQuxc2tiqtZAd1KBKW020KeUGpsiViuQojQZCX4ZAHP9ZB0ieUux1PZCdlJLK';
$app_id = '642299609555471';
$app_secret = '39a2ad1358133e30c6a004b71532e5ce';
// should begin with "act_" (eg: $account_id = 'act_1234567890';)
$account_id = 'act_1171026789724206';
require __DIR__ . '/vendor/autoload.php';
// Configurations - End

if (is_null($access_token) || is_null($app_id) || is_null($app_secret)) {
  throw new \Exception(
    'You must set your access token, app id and app secret before executing'
  );
}

if (is_null($account_id)) {
  throw new \Exception(
    'You must set your account id before executing');
}

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;

Api::init($app_id, $app_secret, $access_token);

// Create the CurlLogger
$logger = new CurlLogger();

// To write to a file pass in a file handler
// $logger = new CurlLogger(fopen('test','w'));

// Attach the logger to the Api instance
Api::instance()->setLogger($logger);

use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdAccountFields;

$account = (new AdAccount($account_id))->read(array(
  AdAccountFields::ID,
  AdAccountFields::NAME,
  AdAccountFields::ACCOUNT_STATUS,
));

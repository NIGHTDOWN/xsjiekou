<?php
/**
 * Copyright (c) 2015-present, Facebook, Inc. All rights reserved.
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
 */

require __DIR__ . '/vendor/autoload.php';

use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Campaign;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;

/*$access_token = '<ACCESS_TOKEN>';
$app_secret = '<APP_SECRET>';
$app_id = '<APP_ID>';
$id = '<AD_ACCOUNT_ID>';*/
$access_token = 'EAAJIKwgRIg8BAJitK7kTZC6UHwQXDANsZCoMjKdURI1fZCG8EFozYL63av6xjoVQZBuQgDUQTj87QXevBTXZCvaE7XeTqprAkmqc2596SEM48uMalbAiyX1HB9r6byQuxc2tiqtZAd1KBKW020KeUGpsiViuQojQZCX4ZAHP9ZB0ieUux1PZCdlJLK';
$app_id = '642299609555471';
$app_secret = '39a2ad1358133e30c6a004b71532e5ce';
// should begin with "act_" (eg: $account_id = 'act_1234567890';)
$id = 'act_1171026789724206';
$api = Api::init($app_id, $app_secret, $access_token);
$api->setLogger(new CurlLogger());

$fields = array(
  'name',
  'objective',
);
$params = array(
  'effective_status' => array('ACTIVE','PAUSED'),
);
var_export((new AdAccount($id))->getCampaigns(
  $fields,
  $params
));
die();
echo json_encode((new AdAccount($id))->getCampaigns(
  $fields,
  $params
)->getResponse()->getContent(), JSON_PRETTY_PRINT);
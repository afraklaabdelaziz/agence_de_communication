<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v9/services/recommendation_service.proto

namespace Google\Ads\GoogleAds\V9\Services\ApplyRecommendationOperation;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Parameters to use when applying call extension recommendation.
 *
 * Generated from protobuf message <code>google.ads.googleads.v9.services.ApplyRecommendationOperation.CallExtensionParameters</code>
 */
class CallExtensionParameters extends \Google\Protobuf\Internal\Message
{
    /**
     * Call extensions to be added. This is a required field.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v9.common.CallFeedItem call_extensions = 1;</code>
     */
    private $call_extensions;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Ads\GoogleAds\V9\Common\CallFeedItem[]|\Google\Protobuf\Internal\RepeatedField $call_extensions
     *           Call extensions to be added. This is a required field.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Ads\GoogleAds\V9\Services\RecommendationService::initOnce();
        parent::__construct($data);
    }

    /**
     * Call extensions to be added. This is a required field.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v9.common.CallFeedItem call_extensions = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getCallExtensions()
    {
        return $this->call_extensions;
    }

    /**
     * Call extensions to be added. This is a required field.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v9.common.CallFeedItem call_extensions = 1;</code>
     * @param \Google\Ads\GoogleAds\V9\Common\CallFeedItem[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setCallExtensions($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Ads\GoogleAds\V9\Common\CallFeedItem::class);
        $this->call_extensions = $arr;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CallExtensionParameters::class, \Google\Ads\GoogleAds\V9\Services\ApplyRecommendationOperation_CallExtensionParameters::class);


<?php

namespace React\Nntp\Response;

interface ResponseInterface
{
    // Connection constants
    const READY_POSTING_ALLOWED         = 200;
    const READY_POSTING_PROHIBITED      = 201;
    const SLAVE_RECOGNIZED              = 202;

    // Common error constants
    const UNKNOWN_COMMAND               = 500;
    const SYNTAX_ERROR                  = 501;
    const NOT_PERMITTED                 = 502;
    const NOT_SUPPORTED                 = 503;

    // Group selection constants
    const GROUP_SELECTED                = 211;
    const NO_SUCH_GROUP                 = 411;

    // Article retrieval constants
    const ARTICLE_FOLLOWS               = 220;
    const HEAD_FOLLOWS                  = 221;
    const BODY_FOLLOWS                  = 222;
    const ARTICLE_SELECTED              = 223;
    const NO_GROUP_SELECTED             = 412;
    const NO_ARTICLE_SELECTED           = 420;
    const NO_NEXT_ARTICLE               = 421;
    const NO_PREVIOUS_ARTICLE           = 422;
    const NO_SUCH_ARTICLE_NUMBER        = 423;
    const NO_SUCH_ARTICLE_ID            = 430;

    // Transferring constants
    const TRANSFER_SEND                 = 335;
    const TRANSFER_SUCCESS              = 235;
    const TRANSFER_UNWANTED             = 435;
    const TRANSFER_FAILURE              = 436;
    const TRANSFER_REJECTED             = 437;

    // Posting constants
    const POSTING_SEND                  = 340;
    const POSTING_SUCCESS               = 240;
    const POSTING_PROHIBITED            = 440;
    const POSTING_FAILURE               = 441;

    // Authorization constants
    const AUTHORIZATION_REQUIRED        = 450;
    const AUTHORIZATION_CONTINUE        = 350;
    const AUTHORIZATION_ACCEPTED        = 250;
    const AUTHORIZATION_REJECTED        = 452;

    // Authentication constants
    const AUTHENTICATION_REQUIRED       = 480;
    const AUTHENTICATION_CONTINUE       = 381;
    const AUTHENTICATION_ACCEPTED       = 281;
    const AUTHENTICATION_REJECTED       = 482;

    // Miscellanious constants
    const HELP_FOLLOWS                  = 100;
    const CAPABILITIES_FOLLOW           = 101;
    const SERVER_DATE                   = 111;
    const GROUPS_FOLLOW                 = 215;
    const OVERVIEW_FOLLOWS              = 224;
    const HEADERS_FOLLOW                = 225;
    const NEW_ARTICLES_FOLLOW           = 230;
    const NEW_GROUPS_FOLLOW             = 231;
    const WRONG_MODE                    = 401;
    const INTERNAL_FAULT                = 403;
    const BASE64_ENCODING_ERROR         = 504;

    /**
     * Get the status code of the response
     *
     * @return integer
     */
    public function getStatusCode();

    /**
     * Get the response message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Check if the response is multiline response
     *
     * @return Boolean
     */
    public function isMultilineResponse();
}

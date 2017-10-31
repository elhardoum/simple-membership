<?php

namespace App;

final class Config
{
    /**
      * Basic settings
      */
    const SITE_NAME = 'Simple Membership';

    /**
      * Database credentials
      */

    // mysql db
    const DB_NAME = 'simple_membership';

    // mysql host
    const DB_HOST = 'localhost';

    // mysql user
    const DB_USER = 'local';

    // mysql user password
    const DB_PASS = '';

    /**
      * Security
      */

    // some random salt for auth hashing
    const AUTH_SALT = '|SVudQ!d12IwRNFj/~y#N+_*yOI* gw?+&0q)D<*=V]u3SMdILVW*)~Cq0;p2KkM#S|Kl>xYgn1qx%!|8TAw}';

    // some random salt for general use
    const SALT = 'Wxen!HEE^bPdr.rUfhdaZNLM6cz_tpi,JO$!AG>0e0&S^_AUTH_SALTe4s-nOC35_d+{D=awdfjYN1{2=RBH6';

    /**
      * Membership details
      */

    // Price in USD
    const MEMBERSHIP_PRICE = 4.99;

    // Duration of a membership
    const MEMBERSHIP_DURATION = YEAR_IN_SECONDS; // = 1 year

    /**
      * PayPal config
      */

    // toggle on/off sandbox mode
    const PAYPAL_SANDBOX_MODE = false;

    // paypal btn ID
    const PAYPAL_HOSTED_BUTTON_ID = '94U2XNW32HCPL';

    // paypal unsubscribe alias (from the unsubscribe button)
    const PAYPAL_UNSUBSCRIBE_ALIAS = 'SDTL5ZKSX3NHQ';

    // this requires no modification
    const PAYPAL_UNSUBSCRIBE_LINK = 'https://www' . (
        Config::PAYPAL_SANDBOX_MODE ? '.sandbox' : ''
    ) . '.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=' . Config::PAYPAL_UNSUBSCRIBE_ALIAS;

    // isolate the app while receiving IPN threads
    const PAYPAL_PLAN_ID = 'paypal-recurring-payments';

    /**
      * Mail settings
      */

    /* SMTP */
    // enable SMTP
    const MAIL_USE_SMTP = true;
    const SEND_WELCOME_EMAIL = false;

    // edit if you enable smtp
    const MAIL_SMTP_HOST = '';
    const MAIL_SMTP_AUTH = true;
    const MAIL_SMTP_USER = '';
    const MAIL_SMTP_SECRET = '';
    const MAIL_SMTP_SECURE = 'ssl';
    const MAIL_SMTP_PORT = 465;
    const MAIL_FROM_EMAIL = '';
    const MAIL_FROM_NAME = Config::SITE_NAME;
    const MAIL_REPLY_TO_EMAIL = Config::MAIL_FROM_EMAIL;
    const MAIL_REPLY_TO_NAME = Config::SITE_NAME;

    
    /**
      * Leave these untouched
      */
    const MINUTE_IN_SECONDS = 60;
    const HOUR_IN_SECONDS = 60 * Config::MINUTE_IN_SECONDS;
    const DAY_IN_SECONDS = 24 * Config::HOUR_IN_SECONDS;
    const WEEK_IN_SECONDS = 7 * Config::DAY_IN_SECONDS;
    const MONTH_IN_SECONDS = 30 * Config::DAY_IN_SECONDS;
    const YEAR_IN_SECONDS = 365 * Config::DAY_IN_SECONDS;
}
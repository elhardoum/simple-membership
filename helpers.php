<?php

defined ( 'APP_DIR' ) || exit('Direct access not allowed.');

use App\Cookie, App\View, App\Errors, App\DB;
use Nonce\Nonce;

function cfg($id, $default=null) {
    if ( defined($id) ) {
        return constant($id);
    } else if ( defined("App\Config::{$id}") ) {
        return constant("App\Config::{$id}");
    } else {
        return $default;
    }
}

function is_email( $email ) {
    if ( strlen( $email ) < 3 ) {
        return false;
    }

    if ( false === strpos( $email, '@', 1 ) ) {
        return false;
    }
 
    list( $local, $domain ) = explode( '@', $email, 2 );
 
    if ( !preg_match( '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local ) ) {
        return false;
    }
 
    if ( preg_match( '/\.{2,}/', $domain ) ) {
        return false;
    }

    if ( trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) {
        return false;
    }
 
    $subs = explode( '.', $domain );
 
    if ( 2 > count( $subs ) ) {
        return false;
    }
 
    foreach ( $subs as $sub ) {
        if ( trim( $sub, " \t\n\r\0\x0B-" ) !== $sub ) {
            return false;
        }
 
        if ( !preg_match('/^[a-z0-9-]+$/i', $sub ) ) {
            return false;
        }
    }
    
    return $email; 
}

function esc_url( $url ) {
    $url = htmlspecialchars($url);
 
    return $url;
}

function validate_redirect($location, $default = '') {
    if ( !$default || !trim($default) )
        $default = url();

    $location = trim( $location );
    // browsers will assume 'http' is your protocol, and will obey a redirect to a URL starting with '//'
    if ( substr($location, 0, 2) == '//' )
        $location = 'http:' . $location;
 
    // In php 5 parse_url may fail if the URL query part contains http://, bug #38143
    $test = ( $cut = strpos($location, '?') ) ? substr( $location, 0, $cut ) : $location;
 
    // @-operator is used to prevent possible warnings in PHP < 5.3.3.
    $lp = @parse_url($test);
 
    // Give up if malformed URL
    if ( false === $lp )
        return $default;
 
    // Allow only http and https schemes. No data:, etc.
    if ( isset($lp['scheme']) && !('http' == $lp['scheme'] || 'https' == $lp['scheme']) )
        return $default;
 
    // Reject if certain components are set but host is not. This catches urls like https:host.com for which parse_url does not set the host field.
    if ( ! isset( $lp['host'] ) && ( isset( $lp['scheme'] ) || isset( $lp['user'] ) || isset( $lp['pass'] ) || isset( $lp['port'] ) ) ) {
        return $default;
    }
 
    // Reject malformed components parse_url() can return on odd inputs.
    foreach ( array( 'user', 'pass', 'host' ) as $component ) {
        if ( isset( $lp[ $component ] ) && strpbrk( $lp[ $component ], ':/?#@' ) ) {
            return $default;
        }
    }
 
    $wpp = parse_url(url());
    $allowed_hosts = array($wpp['host']);
 
    if ( preg_match('/^www\./si', $allowed_hosts[0]) ) {
        $allowed_hosts[] = preg_replace('/^www\./si', '', DOMAIN);
    } else {
        $allowed_hosts[] = 'www.' . DOMAIN;
    }

    if ( isset($lp['host']) && ( !in_array($lp['host'], $allowed_hosts) && $lp['host'] != strtolower($wpp['host'])) )
        $location = $default;
 
    return $location;
}

function check_invalid_utf8( $string, $strip = false ) {
    $string = (string) $string;
 
    if ( 0 === strlen( $string ) ) {
        return '';
    }
 
    static $utf8_pcre = null;
    if ( ! isset( $utf8_pcre ) ) {
        $utf8_pcre = @preg_match( '/^./u', 'a' );
    }

    if ( !$utf8_pcre ) {
        return $string;
    }
 
    if ( 1 === @preg_match( '/^./us', $string ) ) {
        return $string;
    }
 
    if ( $strip && function_exists( 'iconv' ) ) {
        return iconv( 'utf-8', 'utf-8', $string );
    }
 
    return '';
}

function _specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false ) {
    $string = (string) $string;
 
    if ( 0 === strlen( $string ) )
        return '';
 
    // Don't bother if there are no specialchars - saves some processing
    if ( ! preg_match( '/[&<>"\']/', $string ) )
        return $string;
 
    // Account for the previous behaviour of the function when the $quote_style is not an accepted value
    if ( empty( $quote_style ) )
        $quote_style = ENT_NOQUOTES;
    elseif ( ! in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) )
        $quote_style = ENT_QUOTES;
 
    if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ) ) )
        $charset = 'UTF-8';
 
    $_quote_style = $quote_style;
 
    if ( $quote_style === 'double' ) {
        $quote_style = ENT_COMPAT;
        $_quote_style = ENT_COMPAT;
    } elseif ( $quote_style === 'single' ) {
        $quote_style = ENT_NOQUOTES;
    }
 
    $string = @htmlspecialchars( $string, $quote_style, $charset, $double_encode );
 
    // Back-compat.
    if ( 'single' === $_quote_style )
        $string = str_replace( "'", '&#039;', $string );
 
    return $string;
}

function esc_attr( $text ) {
    $safe_text = check_invalid_utf8( $text );
    $safe_text = _specialchars( $safe_text, ENT_QUOTES );
    return $safe_text;
}

function old($param, $default=null, $req='REQUEST') {
    switch (strtolower($req)) {
        case 'post':
            $data = $_POST;
            break;

        case 'get':
            $data = $_GET;
            break;

        case 'request':
            $data = $_REQUEST;
            break;
        
        default:
            $data = null;
            break;
    }

    if ( isset($GLOBALS['old_' . $param]) ) {
        $value = $GLOBALS['old_' . $param];
    } else if ( $data && isset($data[$param]) ) {
        $value = $data[$param];
    } else {
        $value = $default;
    }

    return $value;
}

function set_old($name, $value) {
    $name = 'old_' . $name;

    if ( is_null($value) )
        $value = '';

    $GLOBALS[$name] = $value;
}

if ( !function_exists('maybe_serialize') ) :
function maybe_serialize( $data ) {
    if ( is_array( $data ) || is_object( $data ) )
        return serialize( $data );
 
    if ( is_serialized( $data, false ) )
        return serialize( $data );
 
    return $data;
}
endif;

if ( !function_exists('maybe_unserialize') ) :
function maybe_unserialize( $original ) {
    if ( is_serialized( $original ) )
        return @unserialize( $original );
    return $original;
}
endif;

if ( !function_exists('is_serialized') ) :
function is_serialized( $data, $strict = true ) {
    // if it isn't a string, it isn't serialized.
    if ( ! is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( 'N;' == $data ) {
        return true;
    }
    if ( strlen( $data ) < 4 ) {
        return false;
    }
    if ( ':' !== $data[1] ) {
        return false;
    }
    if ( $strict ) {
        $lastc = substr( $data, -1 );
        if ( ';' !== $lastc && '}' !== $lastc ) {
            return false;
        }
    } else {
        $semicolon = strpos( $data, ';' );
        $brace     = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace )
            return false;
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 )
            return false;
        if ( false !== $brace && $brace < 4 )
            return false;
    }
    $token = $data[0];
    switch ( $token ) {
        case 's' :
            if ( $strict ) {
                if ( '"' !== substr( $data, -2, 1 ) ) {
                    return false;
                }
            } elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
            // or else fall through
        case 'a' :
        case 'O' :
            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b' :
        case 'i' :
        case 'd' :
            $end = $strict ? '$' : '';
            return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
    }
    return false;
}
endif;

function _parse_str( $string, &$array ) {
    parse_str( $string, $array );
    if ( get_magic_quotes_gpc() )
        $array = stripslashes_deep( $array );
}

function stripslashes_deep( $value ) {
    return map_deep( $value, 'stripslashes_from_strings_only' );
}

function map_deep( $value, $callback ) {
    if ( is_array( $value ) ) {
        foreach ( $value as $index => $item ) {
            $value[ $index ] = map_deep( $item, $callback );
        }
    } elseif ( is_object( $value ) ) {
        $object_vars = get_object_vars( $value );
        foreach ( $object_vars as $property_name => $property_value ) {
            $value->$property_name = map_deep( $property_value, $callback );
        }
    } else {
        $value = call_user_func( $callback, $value );
    }
 
    return $value;
}

function stripslashes_from_strings_only( $value ) {
    return is_string( $value ) ? stripslashes( $value ) : $value;
}

function urlencode_deep( $value ) {
    return map_deep( $value, 'urlencode' );
}

function sanitize_text($str, $keep_newlines=false) {
    $filtered = check_invalid_utf8( $str );
 
    if ( strpos($filtered, '<') !== false ) {
        $filtered = preg_replace_callback('%<[^>]*?((?=<)|>|$)%', function($matches) {
            if ( false === strpos($matches[0], '>') )
                return esc_html($matches[0]);
            return $matches[0];
        }, $filtered);

        $filtered = strip_all_tags( $filtered, false ); 
        $filtered = str_replace("<\n", "&lt;\n", $filtered);
    }
 
    if ( ! $keep_newlines ) {
        $filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
    }
    $filtered = trim( $filtered );
 
    $found = false;
    while ( preg_match('/%[a-f0-9]{2}/i', $filtered, $match) ) {
        $filtered = str_replace($match[0], '', $filtered);
        $found = true;
    }
 
    if ( $found ) {
        // Strip out the whitespace that may now exist after removing the octets.
        $filtered = trim( preg_replace('/ +/', ' ', $filtered) );
    }
 
    return $filtered;
}

function strip_all_tags($string, $remove_breaks = false) {
    $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
    $string = strip_tags($string);
 
    if ( $remove_breaks )
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
 
    return trim( $string );
}

function esc_html( $text ) {
    $safe_text = check_invalid_utf8( $text );
    $safe_text = _specialchars( $safe_text, ENT_QUOTES );
    return $safe_text;
}

function phpmailer() {
    global $phpmailer;

    if ( isset($phpmailer) && is_object($phpmailer) ) {
        return $phpmailer;
    }

    $phpmailer = new PHPMailer;

    if ( cfg('MAIL_USE_SMTP') ) {
        $phpmailer->isSMTP();
        $phpmailer->Host = cfg('MAIL_SMTP_HOST');
        $phpmailer->SMTPAuth = cfg('MAIL_SMTP_AUTH') ? cfg('MAIL_SMTP_AUTH') : true;
        $phpmailer->Username = cfg('MAIL_SMTP_USER');
        $phpmailer->Password = cfg('MAIL_SMTP_SECRET');
        $phpmailer->SMTPSecure = defined('MAIL_SMTP_SECURE') ? cfg('MAIL_SMTP_SECURE') : 'tls';
        $phpmailer->Port = cfg('MAIL_SMTP_PORT') ? cfg('MAIL_SMTP_PORT') : 587;
    }

    if ( cfg('MAIL_FROM_EMAIL') ) {
        $args = array(cfg('MAIL_FROM_EMAIL'));

        if ( cfg('MAIL_FROM_NAME') ) {
            $args[] = cfg('MAIL_FROM_NAME');
        }

        call_user_func_array(array($phpmailer, 'setFrom'), $args);
    }

    if ( cfg('MAIL_FROM_EMAIL') ) {
        $args = array(cfg('MAIL_REPLY_TO_EMAIL'));

        if ( cfg('MAIL_REPLY_TO_NAME') ) {
            $args[] = cfg('MAIL_REPLY_TO_NAME');
        }

        call_user_func_array(array($phpmailer, 'addReplyTo'), $args);
    }

    return $phpmailer;
}

function send_mail($to, $subject, $body, $args=null) {
    $mail = phpmailer();

    if ( is_string($to) ) {
        if ( strpos($to, ':') ) {
            list($name, $email) = explode(':', $to);
        } else {
            $email = $to;
            $name = null;
        }
        $n = [$email];
        if ( $name && trim($name) ) {
            $n[] = $name;
        }
        if ( $email ) {
            call_user_func_array([$mail, 'addAddress'], $n);
        }
    } else if ( is_array($to) ) {
        if ( strpos($to[0], ':') ) {
            list($name, $email) = explode(':', $to[0]);
        } else {
            $email = $to[0];
            $name = null;
        }
        $n = [$email];
        if ( $name && trim($name) ) {
            $n[] = $name;
        }
        if ( $email ) {
            call_user_func_array([$mail, 'addAddress'], $n);
            if ( count($to) > 1 ) {
                foreach ( array_slice($to, 1) as $_to ) {
                    if ( strpos($_to, ':') ) {
                        list($name, $email) = explode(':', $_to);
                    } else {
                        $email = $_to;
                        $name = null;
                    }
                    $n = [$email];
                    if ( $name && trim($name) ) {
                        $n[] = $name;
                    }
                    if ( $email ) {
                        call_user_func_array([$mail, 'AddCC'], $n);
                    }
                }
            }
        }      
    }

    if ( isset($args['html']) ) {
        $mail->isHTML((bool) $args['html']);
    } else {
        $mail->isHTML();
    }

    if ( isset($args['attachements']) ) {
        if ( !is_array((array) $args['attachements']) )
            $args['attachements'] = array($args['attachements']);

        foreach ((array) $args['attachements'] as $att) {
            $mail->addAttachment($att);
        }
    }

    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AltBody = strip_all_tags($body);

    // //debug
    $mail->SMTPDebug = 2;
    $mail->Timeout = 15;

    try
    {
        return $mail->send();
    } catch ( Exception $e )
    {
        // print '<pre>';
        // print_r($e->getMessage());
        // print_r($mail->ErrorInfo);
        // print '</pre>';
    }
}

function parse_redirect_data($methods=null, $handle='set_old') {
    $c = $_COOKIE;

    if ( empty($c) )
        return;

    $data = [];

    foreach ( (array) $c as $n=>$v ) {
        if ( !preg_match ( '/^_rdr_/si', $n ) ) {
            unset($c[$n]);
            continue;
        }

        $value = Cookie::get($n);

        if ( !$value )
            continue;

        $data[preg_replace('/^_rdr_/si', '', $n)] = $value;
        Cookie::delete($n);
    }

    if ( $data && $methods ) {
        foreach ( $data as $n=>$v ) {
            foreach ( (array) $methods as $method ) {
                switch ( strtolower($method) ) {
                    case 'get':
                        $_GET[$n] = $v;
                        break;

                    case 'post':
                        $_POST[$n] = $v;
                        break;

                    case 'request':
                        $_REQUEST[$n] = $v;
                        break;

                    case 'cookie':
                        $_COOKIE[$n] = $v;
                        break;
                }
            }
        }
    }

    if ( $data && $handle && is_callable($handle) ) {
        foreach ( $data as $n=>$v ) {
            call_user_func($handle, $n, $v);
        }
    }

    return $data;
}

function redirect($to, $args=null) {
    return View::redirect($to, $args);
}

function url($after=null, $relative=null) {
    return View::url($after, $relative);
}

function print_field_errors(Errors $errors, $code, $not_codes=null) {
    ?>
        <?php if ( $err = $errors->getErrors(null, null, $code) ) : ?>
            <ul class="errors">
                <?php foreach ( $err as $error ) : ?>
                    <li class="error<?php echo isset($error[1]) ? " type-{$error[1]}" : null; ?>">
                        <?php echo decode_str(array_shift($error)); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php
}

function print_errors(Errors $errors) {
    ?>
        <?php if ( $err = call_user_func_array(array($errors, 'getErrors'), array_slice(func_get_args(), 1)) ) : ?>
            <ul class="errors">
                <?php foreach ( $err as $error ) : ?>
                    <li class="error<?php echo isset($error[1]) ? " type-{$error[1]}" : null; ?>">
                        <?php echo decode_str(array_shift($error)); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php
}

function decode_str($str) {
    $str = html_entity_decode($str, ENT_QUOTES);
    $str = html_entity_decode($str);
    return $str;
}

function metaphp_database_instance($close=null) {
    return DB::i($close);
}

function nonce() { return Nonce::instance(); }
function bad_auth(Errors &$err) { return $err->addError('Error occured, could not verify the form credentials, please try again', 'error'); }
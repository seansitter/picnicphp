<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Script_CLI
{
    public function promptYesNo($message, $default = null)
    {
        $yes = 'y';
        $no = 'n';
        if (is_string($default) or is_bool($default)) {
            if ((strtolower($default) == 'y') or (true === $default)) {
                $yes = 'Y';
                $default = true;
            }
            if ((strtolower($default) == 'n') or (false === $default)){
                $no = 'N';
                $default = false;
            }
        }
        while (true) {
            echo $message." [$yes/$no]: ";
            $line = strtolower(trim(fgets(STDIN)));
            if (('y' === $line) or ('yes' == $line)) {
                return true;
            }
            if (('n' == $line) or ('no' == $line)){
                return false;
            }
            if (is_bool($default)) {
                return $default;
            }

        }

    }

    public function promptWithMessage($message, $default = null) {
        if (!is_null($default)) {
            $message .= " [{$default}]: ";
        } else {
            $message .= ": ";
        }

        while (true) {
            echo $message;
            $line = trim(fgets(STDIN));
            if (empty($line) and !is_null($default)) {
                return $default;
            } elseif(!empty($line)) {
                return $line;
            }
        }
    }

    /**
     * A pure php implementation of getopt.
     * 
     * @copyright http://www.ntu.beautifulworldco.com/weblog/?p=526
     * @see http://docs.php.net/manual/en/function.getopt.php
     */    
    public static function getopt()
    {

        /* getopt(): Ver. 1.2      2008/08/19
         My page: http://www.ntu.beautifulworldco.com/weblog/?p=526

         Usage: getopt ( [$flag,] $short_option [, $long_option] );

         Note that another function split_para() is required, which can be found in the same
         page.

         getopt() fully simulates getopt() which is described at
         http://us.php.net/manual/en/function.getopt.php , including long options for PHP
         version under 5.3.0. (Prior to 5.3.0, long options was only available on few systems)

         Besides legacy usage of getopt(), I also added a new option to manipulate your own
         argument lists instead of those from command lines. This new option can be a string
         or an array such as

         $flag = "-f value_f -ab --required 9 --optional=PK --option -v test -k";
         or
         $flag = array ( "-f", "value_f", "-ab", "--required", "9", "--optional=PK", "--option" );

         So there are four ways to work with getopt(),

         1. getopt ( $short_option );

         it's a legacy usage, same as getopt ( $short_option ).

         2. getopt ( $short_option, $long_option );

         it's a legacy usage, same as getopt ( $short_option, $long_option ).

         3. getopt ( $flag, $short_option );

         use your own argument lists instead of command line arguments.

         4. getopt ( $flag, $short_option, $long_option );

         use your own argument lists instead of command line arguments.

         */

        if (func_num_args() == 1) {
            $flag =  $flag_array = $GLOBALS['argv'];
            $short_option = func_get_arg (0);
            $long_option = array ();
        } else if (func_num_args() == 2) {
            if (is_array (func_get_arg(1))) {
                $flag = $GLOBALS['argv'];
                $short_option = func_get_arg(0);
                $long_option = func_get_arg (1);
            } else {
                $flag = func_get_arg(0);
                $short_option = func_get_arg(1);
                $long_option = array();
            }
        } else if (func_num_args() == 3) {
            $flag = func_get_arg(0);
            $short_option = func_get_arg(1);
            $long_option = func_get_arg(2);
        } else {
            exit("wrong options\n");
        }

        $short_option = trim($short_option);

        $short_no_value = array();
        $short_required_value = array();
        $short_optional_value = array();
        $long_no_value = array();
        $long_required_value = array();
        $long_optional_value = array();
        $options = array();

        for ($i = 0; $i < strlen ($short_option); ) {
            if (($short_option{$i} != ":")) {
                if (!isset($short_option{$i+1}) or ($short_option{$i+1} != ":")) {
                    $short_no_value[] = $short_option{$i};
                    $i++;
                    continue;
                } else if ($short_option{$i+1} == ":" && $short_option{$i+2} != ":") {
                    $short_required_value[] = $short_option{$i};
                    $i += 2;
                    continue;
                } else if ($short_option{$i+1} == ":" && $short_option{$i+2} == ":") {
                    $short_optional_value[] = $short_option{$i};
                    $i += 3;
                    continue;
                }
            } else {
                continue;
            }
        }

        foreach ($long_option as $a) {
            if (substr( $a, -2 ) == "::") {
                $long_optional_value[] = substr( $a, 0, -2);
                continue;
            } else if (substr( $a, -1 ) == ":") {
                $long_required_value[] = substr($a, 0, -1);
                continue;
            } else {
                $long_no_value[] = $a;
                continue;
            }
        }

        if ( is_array ( $flag ) )
        $flag_array = $flag;
        else {
            $flag = "- $flag";
            $flag_array = self::split_para( $flag );
        }

        for ( $i = 0; $i < count( $flag_array ); ) {

            if ( $i >= count ( $flag_array ) )
            break;

            if ( ! $flag_array[$i] || $flag_array[$i] == "-" ) {
                $i++;
                continue;
            }

            if ( $flag_array[$i]{0} != "-" ) {
                $i++;
                continue;

            }

            if ( substr( $flag_array[$i], 0, 2 ) == "--" ) {

                if (strpos($flag_array[$i], '=') != false) {
                    list($key, $value) = explode('=', substr($flag_array[$i], 2), 2);
                    if ( in_array ( $key, $long_required_value ) || in_array ( $key, $long_optional_value ) )
                    $options[$key][] = $value;
                    $i++;
                    continue;
                }

                if (strpos($flag_array[$i], '=') == false) {
                    $key = substr( $flag_array[$i], 2 );
                    if ( in_array( substr( $flag_array[$i], 2 ), $long_required_value ) ) {
                        $options[$key][] = $flag_array[$i+1];
                        $i += 2;
                        continue;
                    } else if ( in_array( substr( $flag_array[$i], 2 ), $long_optional_value ) ) {
                        if ( $flag_array[$i+1] != "" && $flag_array[$i+1]{0} != "-" ) {
                            $options[$key][] = $flag_array[$i+1];
                            $i += 2;
                        } else {
                            $options[$key][] = FALSE;
                            $i ++;
                        }
                        continue;
                    } else if ( in_array( substr( $flag_array[$i], 2 ), $long_no_value ) ) {
                        $options[$key][] = FALSE;
                        $i++;
                        continue;
                    } else {
                        $i++;
                        continue;
                    }
                }

            } else if ( $flag_array[$i]{0} == "-" && $flag_array[$i]{1} != "-" ) {

                for ( $j=1; $j < strlen($flag_array[$i]); $j++ ) {
                    if ( in_array( $flag_array[$i]{$j}, $short_required_value ) || in_array( $flag_array[$i]{$j}, $short_optional_value )) {

                        if ( $j == strlen($flag_array[$i]) - 1  ) {
                            if ( in_array( $flag_array[$i]{$j}, $short_required_value ) ) {
                                $options[$flag_array[$i]{$j}][] = $flag_array[$i+1];
                                $i += 2;
                            } else if ( in_array( $flag_array[$i]{$j}, $short_optional_value ) && $flag_array[$i+1] != "" && $flag_array[$i+1]{0} != "-" ) {
                                $options[$flag_array[$i]{$j}][] = $flag_array[$i+1];
                                $i += 2;
                            } else {
                                $options[$flag_array[$i]{$j}][] = FALSE;
                                $i ++;
                            }
                            $plus_i = 0;
                            break;
                        } else {
                            $options[$flag_array[$i]{$j}][] = substr ( $flag_array[$i], $j + 1 );
                            $i ++;
                            $plus_i = 0;
                            break;
                        }

                    } else if ( in_array ( $flag_array[$i]{$j}, $short_no_value ) ) {

                        $options[$flag_array[$i]{$j}][] = FALSE;
                        $plus_i = 1;
                        continue;

                    } else {
                        $plus_i = 1;
                        break;
                    }
                }

                $i += $plus_i;
                continue;

            }

            $i++;
            continue;
        }

        foreach ( $options as $key => $value ) {
            if ( count ( $value ) == 1 ) {
                $options[ $key ] = $value[0];

            }

        }

        return $options;

    }

    private static function split_para ( $pattern ) {
        /* split_para() version 1.0      2008/08/19
         My page: http://www.ntu.beautifulworldco.com/weblog/?p=526

         This function is to parse parameters and split them into smaller pieces.
         preg_split() does similar thing but in our function, besides "space", we
         also take the three symbols " (double quote), '(single quote),
         and \ (backslash) into consideration because things in a pair of " or '
         should be grouped together.

         As an example, this parameter list

         -f "test 2" -ab --required "t\"est 1" --optional="te'st 3" --option -v 'test 4'

         will be splited into

         -f
         t"est 2
         -ab
         --required
         test 1
         --optional=te'st 3
         --option
         -v
         test 4

         see the code below,

         $pattern = "-f \"test 2\" -ab --required \"t\\\"est 1\" --optional=\"te'st 3\" --option -v 'test 4'";

         $result = split_para( $pattern );

         echo "ORIGINAL PATTERN: $pattern\n\n";

         var_dump( $result );

         */

        $begin=0;
        $backslash = 0;
        $quote = "";
        $quote_mark = array();
        $result = array();

        $pattern = trim ( $pattern );

        for ( $end = 0; $end < strlen ( $pattern ) ; ) {

            if ( ! in_array ( $pattern{$end}, array ( " ", "\"", "'", "\\" ) ) ) {
                $backslash = 0;
                $end ++;
                continue;
            }

            if ( $pattern{$end} == "\\" ) {
                $backslash++;
                $end ++;
                continue;
            } else if ( $pattern{$end} == "\"" ) {
                if ( $backslash % 2 == 1 || $quote == "'" ) {
                    $backslash = 0;
                    $end ++;
                    continue;
                }

                if ( $quote == "" ) {
                    $quote_mark[] = $end - $begin;
                    $quote = "\"";
                } else if ( $quote == "\"" ) {
                    $quote_mark[] = $end - $begin;
                    $quote = "";
                }

                $backslash = 0;
                $end ++;
                continue;
            } else if ( $pattern{$end} == "'" ) {
                if ( $backslash % 2 == 1 || $quote == "\"" ) {
                    $backslash = 0;
                    $end ++;
                    continue;
                }

                if ( $quote == "" ) {
                    $quote_mark[] = $end - $begin;
                    $quote = "'";
                } else if ( $quote == "'" ) {
                    $quote_mark[] = $end - $begin;
                    $quote = "";
                }

                $backslash = 0;
                $end ++;
                continue;
            } else if ( $pattern{$end} == " " ) {
                if ( $quote != "" ) {
                    $backslash = 0;
                    $end ++;
                    continue;
                } else {
                    $backslash = 0;
                    $cand = substr( $pattern, $begin, $end-$begin );
                    for ( $j = 0; $j < strlen ( $cand ); $j ++ ) {
                        if ( in_array ( $j, $quote_mark ) )
                        continue;

                        $cand1 .= $cand{$j};
                    }
                    if ( $cand1 ) {
                        eval( "\$cand1 = \"$cand1\";" );
                        $result[] = $cand1;
                    }
                    $quote_mark = array();
                    $cand1 = "";
                    $end ++;
                    $begin = $end;
                    continue;
                }
            }
        }

        $cand = substr( $pattern, $begin, $end-$begin );
        for ( $j = 0; $j < strlen ( $cand ); $j ++ ) {
            if ( in_array ( $j, $quote_mark ) )
            continue;

            $cand1 .= $cand{$j};
        }

        eval( "\$cand1 = \"$cand1\";" );

        if ( $cand1 )
        $result[] = $cand1;

        return $result;
    }
}

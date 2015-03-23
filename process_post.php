<?php

/* 
 * Copyright (c) 2015, Kevin Kabatra <kevinkabatra@gmail.com>
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice, 
 *    this list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */

    /**
     * FILTER_SANITIZE_URL Remove all characters except letters, digits and 
     * $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=.
     */       
    if(filter_input(INPUT_SERVER, 'REQUEST_METHOD', 
            FILTER_SANITIZE_URL) === "POST") {
        //On POST Request clear exceptions from $postInputException
        $postInputException = "";
        if(empty(filter_input(INPUT_POST, 'postParameter',
                FILTER_SANITIZE_URL))) {
            $postInputException .= "Error: the request is empty.";
        } else {
            $postParameter = security_input(filter_input(INPUT_POST,
                    'postParameter', FILTER_SANITIZE_URL));
            
            /*
             * The preg_match() function searches a string for pattern, 
             * returning true if the pattern exists, and false otherwise. 
             * 
             * NEVER Allow: Due to Cross Site Scripting 
             *  Misc: 
             *      &: can be used for ASCII codes: &#60 (<) or &lt (<).
             *      <: can be used to complete html, xml, php tags
             * Allow:
             *  Alphabet: a-zA-Z
             *  Numbers: 0-9
             *  Braces: []: for defining array bounds
             *  Misc: 
             *      =: for key/value pair symbol =>
             *      >: for key/value pair symbol =>
             *      ': for defining key and value =  
             *      ,: for delimiter between key/value pairs
             */
            if(!preg_match("/^[a-zA-Z0-9[\]=>,\' ]*$/",
                    $postParameter)) {
                $postInputException .= "Error: post input is unsafe.";
            }
        }                
        
        if($postInputException != "") {            
            echo $postInputException;
        } else {                      
            //declare and set array, to be used for POST parameters
            $arrayPostParameter = null;

            /*
             * Check to see if there are remaining key/value pairs.
             * String must be formatted as follows:
             *     *note - Each key/value pair must end with the delimeter ','
             *     var postParameter = '[' 
             *        + '\'foo\'=>\'bar\','
             *        + '\'baz\'=>\'qux\','
             *        + '\'quux\'=>\'corge\','
             *        + ']';
             */
            while(strpos($postParameter,'=>') !== false) {
                //Find $key
                $intBeginKeyPosition = strpos($postParameter, '\'') + 1;
                $intEndKeyPosition = strpos($postParameter, '=>') - 1;
                $intKeyLength = $intEndKeyPosition - $intBeginKeyPosition;
                $key = substr($postParameter, $intBeginKeyPosition, 
                        $intKeyLength);
                
                //Remove $key from $postParameter
                $postParameter = substr($postParameter, $intEndKeyPosition + 1);
                
                //Find $value
                $intBeginValuePosition = strpos($postParameter, '\'') + 1;
                $intEndValuePosition = strpos($postParameter, ',') - 1;
                $intValueLength = $intEndValuePosition - $intBeginValuePosition;
                $value = substr($postParameter, $intBeginValuePosition, 
                        $intValueLength);
            
                //Remove $value from $postParameter
                $postParameter = substr($postParameter, $intEndValuePosition 
                        + 1);
                
                //Add $key and $value to $arrayPostParameter
                $arrayPostParameter[$key] = $value;
            }
            
            echo var_dump($arrayPostParameter);
        }
    }
    
    /**
     * Calls trim(), and stripslashes() on a string.
     * <p>
     * The trim() function strips unnecessary characters (extra space, tab, 
     * newline) from the user input data. The stripslashes() function removes
     * backslashes (\) from the user input data.
     * 
     * @param {string} $mSubmittedInstream - String variable that was passed
     * @returns {string} $mSubmittedInstream 
     */    
    function security_input($mSubmittedInstream) {
        $mSubmittedInstream = trim($mSubmittedInstream);
        $mSubmittedInstream = stripslashes($mSubmittedInstream);
        return $mSubmittedInstream;
    }

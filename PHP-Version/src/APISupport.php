<?php
/*********************************************************************
 *
 * $Id: APISupport.php 48521 2022-02-03 10:56:31Z mvuilleu $
 *
 * API support functions (aka httpsupport)
 *
 * - - - - - - - - - License information: - - - - - - - - -
 *
 *  Copyright (C) 2022 and beyond by Yoctopuce Sarl, Switzerland.
 *
 *  Yoctopuce Sarl (hereafter Licensor) grants to you a perpetual
 *  non-exclusive license to use, modify, copy and integrate http
 *  file into your software for the sole purpose of interfacing
 *  with Yoctopuce products.
 *
 *  You may reproduce and distribute copies of this file in
 *  source or object form, as long as the sole purpose of this
 *  code is to interface with Yoctopuce products. You must retain
 *  this notice in the distributed source file.
 *
 *  You should refer to Yoctopuce General Terms and Conditions
 *  for additional information regarding your rights and
 *  obligations.
 *
 *  THE SOFTWARE AND DOCUMENTATION ARE PROVIDED "AS IS" WITHOUT
 *  WARRANTY OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING
 *  WITHOUT LIMITATION, ANY WARRANTY OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE, TITLE AND NON-INFRINGEMENT. IN NO
 *  EVENT SHALL LICENSOR BE LIABLE FOR ANY INCIDENTAL, SPECIAL,
 *  INDIRECT OR CONSEQUENTIAL DAMAGES, LOST PROFITS OR LOST DATA,
 *  COST OF PROCUREMENT OF SUBSTITUTE GOODS, TECHNOLOGY OR
 *  SERVICES, ANY CLAIMS BY THIRD PARTIES (INCLUDING BUT NOT
 *  LIMITED TO ANY DEFENSE THEREOF), ANY CLAIMS FOR INDEMNITY OR
 *  CONTRIBUTION, OR OTHER SIMILAR COSTS, WHETHER ASSERTED ON THE
 *  BASIS OF CONTRACT, TORT (INCLUDING NEGLIGENCE), BREACH OF
 *  WARRANTY, OR OTHERWISE.
 *
 *********************************************************************/
declare(strict_types=1);

function parseEnum(string $ystr, $enumDef): int
{
    if(is_numeric($ystr)) {
        return intVal($ystr);
    }
    $res = array_search($ystr, $enumDef);
    if($res !== FALSE) {
        return $res;
    }
    return 0;
}

function parseUInt(string $ystr): int
{
    $xpos = strpos($ystr, 'x');
    if($xpos !== FALSE) {
        return hexdec(substr($ystr, $xpos+1));
    }
    return intVal($ystr);
}

function parseMeasure(string $ystr): float
{
    return floatVal($ystr);
}

function parseStepPos(string $ystr): float
{
    return floatVal($ystr);
}

function parseMove(string $ystr): object
{
    if(preg_match('/^(?<target>-?\d+):(?<msval>\d+)$/', $ystr, $matches)) {
        return (object)[
            'moving' => 1,
            'target' => $matches['target'],
            'ms' => $matches['ms']
        ];
    }
    return (object)[
        'moving' => 0,
        'target' => 0,
        'ms' => 0
    ];
}

function APIBitString(string $bitstring, int $value): string
{
    $nbits = strlen($bitstring);
    for($i = 0; $i < $nbits; $i++) {
        if(($value & 1) == 0) {
            $bitstring[$i] = '.';
        }
        $value >>= 1;
    }
    return '['.$bitstring.']';
}

function APIPassword(VHubServerHTTPRequest $httpReq, string $pwd): string
{
    if($httpReq->getAuthUser() == 'admin') {
        return $pwd;
    } else {
        return '*****';
    }
}

<?php
/*
 Copyright (C) 2016 - Sam Hermans

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class HeyUpdateAPI
{
    private $api_token;
    private $api_url;

    private $res;

    public function __construct($api_token, $api_url)
    {
        $this->api_token = $api_token;
        $this->api_url = $api_url;
    }

    public function Updates($date_start = false, $date_stop = false)
    {
        // default behaviour is to return last 7 days
        if ($date_start == false || $date_stop == false) {
            $updates = $this->doCall('/updates');
        } else {
            $updates = $this->doCall('/updates?start_date=' . $date_start . '&end_date=' . $date_stop);
        }

        // let's re-associate this and make sure that the key is the ID of the update
        // by doing this we will never have duplicate events
        $this->res = [];
        foreach ($updates as $update) {
            $this->res[$update['id']] = $update;
        }

        return $this;
    }

    public function UpdatesByPeriod($period)
    {
        switch ($period) {
            case "day":
                $date_start = date('Y-m-d', strtotime("-1 day"));
                $date_stop = date('Y-m-d', strtotime("now"));
                return $this->Updates($date_start, $date_stop);
                break;
            case "week":
                $date_start = date('Y-m-d', strtotime("-7 days"));
                $date_stop = date('Y-m-d', strtotime("now"));
                return $this->Updates($date_start, $date_stop);
                break;
            case "month":
                // The api only supports batches of 14 days
                // lets just for now assume a month is 28 days
                $date_start = date('Y-m-d', strtotime("-14 days"));
                $date_stop = date('Y-m-d', strtotime("now"));
                $week1 = $this->Updates($date_start, $date_stop)->getRes();

                $date_start = date('Y-m-d', strtotime("-28 days"));
                $date_stop = date('Y-m-d', strtotime("-14 days"));
                $week2 = $this->Updates($date_start, $date_stop)->getRes();

                $this->res = $week1 + $week2;
                return $this;
                break;
            default:
                throw new Exception('Unknown period');
        }
    }

    public function filterByEmail($email) {
        $res_filter = [];

        foreach($this->res as $update) {
            if(strcasecmp($update['user']['email'], $email))
                $res_filter[$update['id']] = $update;
        }
        $this->res = $res_filter;

        return $this;
    }

    public function filterByName($name) {
        $res_filter = [];

        foreach($this->res as $update) {
            if(strcasecmp($update['user']['name'], $name))
                $res_filter[$update['id']] = $update;
        }
        $this->res = $res_filter;

        return $this;
    }

    public function getRes()
    {
        return $this->res;
    }

    private function doCall($path)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url . $path);
        curl_setopt($ch, CURLOPT_VERBOSE, false); // enable this to debug
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // leave this to true to avoid man in the middle attacks
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // leave this to true to avoid man in the middle attacks
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->api_token));
        return json_decode(curl_exec($ch), true);
    }
}

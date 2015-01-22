<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function jeeserial_install() {
	if (jeeserial::deamonRunning()) {
        jeeserial::stopDeamon();
    }
	// $cron = cron::byClassAndFunction('jeeserial', 'CalculateOtherStats');
    // if (!is_object($cron)) {
        // $cron = new cron();
        // $cron->setClass('jeeserial');
        // $cron->setFunction('CalculateOtherStats');
        // $cron->setEnable(1);
        // $cron->setDeamon(0);
        // $cron->setSchedule('10 0 * * *');
        // $cron->save();
    // }
	
	// $crontoday = cron::byClassAndFunction('jeeserial', 'CalculateTodayStats');
    // if (!is_object($crontoday)) {
        // $crontoday = new cron();
        // $crontoday->setClass('jeeserial');
        // $crontoday->setFunction('CalculateTodayStats');
        // $crontoday->setEnable(1);
        // $crontoday->setDeamon(0);
        // $crontoday->setSchedule('*/5 * * * *');
        // $crontoday->save();
    // }
}

function jeeserial_update() {
	if (jeeserial::deamonRunning()) {
        jeeserial::stopDeamon();
    }
	// $cron = cron::byClassAndFunction('jeeserial', 'CalculateOtherStats');
    // if (!is_object($cron)) {
        // $cron = new cron();
        // $cron->setClass('jeeserial');
        // $cron->setFunction('CalculateOtherStats');
        // $cron->setEnable(1);
        // $cron->setDeamon(0);
        // $cron->setSchedule('10 0 * * *');
        // $cron->save();
    // }
    // $cron->stop();
	
	// $crontoday = cron::byClassAndFunction('jeeserial', 'CalculateTodayStats');
    // if (!is_object($crontoday)) {
        // $crontoday = new cron();
        // $crontoday->setClass('jeeserial');
        // $crontoday->setFunction('CalculateTodayStats');
        // $crontoday->setEnable(1);
        // $crontoday->setDeamon(0);
        // $crontoday->setSchedule('*/5 * * * *');
        // $crontoday->save();
    // }
    // $crontoday->stop();
}

function jeeserial_remove() {
	if (jeeserial::deamonRunning()) {
        jeeserial::stopDeamon();
    }
	// $cron = cron::byClassAndFunction('jeeserial', 'CalculateOtherStats');
    // if (is_object($cron)) {
        // $cron->remove();
    // }
	// $crontoday = cron::byClassAndFunction('jeeserial', 'CalculateTodayStats');
    // if (is_object($crontoday)) {
        // $crontoday->remove();
    // }
}

?>

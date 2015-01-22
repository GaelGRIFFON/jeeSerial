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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class jeeserial extends eqLogic {
    /*     * *************************Attributs****************************** */
    /*     * ***********************Methode static*************************** */
	public static function getJeeSerialInfo($_url){
		return 1;
	}
	
    // Lecture des configurations pré-définies dans jeeserial/core/config/devices
    //  return: tableau des config prédéfinies
	public static function devicesParameters($_device = '') {
        $path = dirname(__FILE__) . '/../config/devices';
        if (isset($_device) && $_device != '') {
            $files = ls($path, $_device . '.php', false, array('files', 'quiet'));
            if (cunt($files) == 1) {
                global $deviceConfiguration;
                require_once($path . '/' . $files[0]);
                return $deviceConfiguration[$_device];
            }
        }
        $files = ls($path, '*.php', false, array('files', 'quiet'));
        $return = array();
        foreach ($files as $file) {
            global $deviceConfiguration;
            require_once($path . '/' . $file);
            $return = $return + $deviceConfiguration;
        }
        if (isset($_device) && $_device != '') {
            if (isset($return[$_device])) {
                return $return[$_device];
            }
            return array();
        }
        return $return;
    }
    
    // Fonction appelée automatiquement par JeeDom aprés la sauvegarde en BDD
    public function postSave() {
        // si on a configuré un device pré-défini, on appelle la fonction qui appliquera la config demandée
        if ($this->getConfiguration('applyDevice') != $this->getConfiguration('device')) {
            $this->applyModuleConfiguration();
        }
    }
    
    // Application de la configuration pré-définie (appelé par postSave)
    public function applyModuleConfiguration() {
        $this->setConfiguration('applyDevice', $this->getConfiguration('device'));
        $this->save();
        if ($this->getConfiguration('device') == '') {
            return true;
        }
        
        // récupération des paramètres
        $device = self::devicesParameters($this->getConfiguration('device'));
        if (!is_array($device)) {
            return true;
        }
        
        // modification du paramétre "configuration"
        if (isset($device['configuration'])) {
            foreach ($device['configuration'] as $key => $value) {
                $this->setConfiguration($key, $value);
            }
        }
        // modification du paramétre "catégory" (lumiètre, automatisme, etc...)
        if (isset($device['category'])) {
            foreach ($device['category'] as $key => $value) {
                $this->setCategory($key, $value);
            }
        }
        // modification du paramétre "isVisible" (bouton "Visible")
        if (isset($device['isVisible'])) {
            $this->setIsVisible($device['isVisible']);
        }
        // modification du paramétre "isEnable" (bouton "Activé")
        if (isset($device['isEnable'])) {
            $this->setIsEnable($device['isEnable']);
        }
        
        // Ajout/Modification des commandes pré-définies
        $cmd_order = 0;
        $link_cmds = array();
        $link_actions = array();
        if (isset($device['commands'])) {
            // si des commandes sont pré-définies
            foreach ($device['commands'] as $command) {
                // on les passe une à une
                $cmd = null;
                foreach ($this->getCmd() as $liste_cmd) {
                    // on regarde si la commande pré-définie à déjà été appliquée
                    if (substr($liste_cmd->getLogicalId(), 0, 1) == $command['logicalId']) {
                        // Traitement des cas avec Volet Roulant (même commande pour chaque sens)
                        if(strrpos($liste_cmd->getName(), "UP") and strrpos($command['name'], "UP")){
                            $cmd = $liste_cmd;
                            break;
                        }
                        if(strrpos($liste_cmd->getName(), "DOWN") and strrpos($command['name'], "DOWN")){
                            $cmd = $liste_cmd;
                            break;
                        }
                        
                        if(!(strrpos($command['name'], "UP")) and !(strrpos($command['name'], "DOWN"))){
                            // Si pas de correspondance UP ou DOWN, on est dans le cas général
                            $cmd = $liste_cmd;
                            break;
                        }
                    }
                }
                
                // Ajout/Modifification des commandes
                try {
                    if ($cmd == null || !is_object($cmd)) {
                        // nouvelle commande
                        $cmd = new jeeserialCmd();
                        $cmd->setOrder($cmd_order);
                        $cmd->setEqLogic_id($this->getId());
                    } else {
                        // commande existante
                        $command['name'] = $cmd->getName();
                    }
                    // Array to Object: on applique la config de la commande sur l'objet de la commande
                    utils::a2o($cmd, $command);
                    
                    // Mise à jour de l'adresse de la commande par rapport au LogicalID de l'équipement
                    $adresseCmd = strval(dechex(hexdec($this->getLogicalId()) + hexdec($command['configuration']['deltaAdresse'])));
                    $newLogicalId = $command['logicalId'] . $adresseCmd;
                    $cmd->setLogicalId($newLogicalId);
                    
                    // sauvegarde de la commande
                    $cmd->save();
                    if (isset($command['value'])) {
                        $link_cmds[$cmd->getId()] = $command['value'];
                    }
                    // if (isset($command['configuration']) && isset($command['configuration']['updateCmdId'])) {
                        // $link_actions[$cmd->getId()] = $command['configuration']['updateCmdId'];
                    // }
                    $cmd_order++;
                } catch (Exception $exc) {
                    
                }
            }
        }
        // Création des lien entre les commandes (retour d'info)
        if (count($link_cmds) > 0) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                foreach ($link_cmds as $cmd_id => $link_cmd) {
                    if ($link_cmd == $eqLogic_cmd->getName()) {
                        $cmd = cmd::byId($cmd_id);
                        if (is_object($cmd)) {
                            $cmd->setValue($eqLogic_cmd->getId());
                            $cmd->save();
                        }
                    }
                }
            }
        }

        $this->save();
    }
	
    // Fonction appelée par JeeDom automatiquement toutes les minutes: relance le démon JeeSerial (python)
	public static function cron() {
		log::add('jeeserial', 'info', 'Cron du daemon jeeSerial');
		$cache = cache::byKey('jeeserial::ancienDaemon', false, true);

		if($cache->getValue(0) != config::byKey('externalDeamon', 'jeeserial', 0)){
			if (self::deamonRunning()) {
                self::stopDeamon();
            }
		}		
		if (config::byKey('jeeNetwork::mode') == 'slave') { //Je suis l'esclave
			if (!self::deamonRunning()) {
                self::runExternalDeamon();
            }	
		}
		else{
			if (config::byKey('externalDeamon', 'jeeserial', 0) == 0) {
                if (!self::deamonRunning()) {
                    self::runDeamon();
                }
                message::removeAll('jeeserial', 'noTeleinfoPort');
			}
		}
		cache::set('jeeserial::ancienDaemon', config::byKey('externalDeamon', 'jeeserial', 0), 0);
		
		self::Calculate_PAPP();
    }
	
    // Fonction qui lance le démon python dans le cas d'une installation distante
	public static function runExternalDeamon() {
        log::add('jeeserial', 'info', 'Mode satellite');
        $jeeserial_path = realpath(dirname(__FILE__) . '/../../ressources');
		$modem_serie_addr = config::byKey('port', 'jeeserial');
		
		if($modem_serie_addr == "serie"){
			$port = config::byKey('modem_serie_addr', 'jeeserial');
			goto lancement;
		}
		$port = jeedom::getUsbMapping(config::byKey('port', 'jeeserial'));
        if (!file_exists($port)) {
			log::add('jeeserial', 'error', 'Le port n\'existe pas');
			goto end;
        }
		lancement:
		$ip_externe = config::byKey('jeeNetwork::master::ip');

		$cle_api = config::byKey('jeeNetwork::master::apikey');
		if($cle_api == ''){
			log::add('jeeserial', 'error', 'Erreur de clé api, veuillez la vérifier.');
			goto end;
		}	
		$cmd = 'nice -n 19 /usr/bin/python ' . $jeeserial_path . '/jeeSerial.py' . ' -p ' . $port . ' -e ' . $ip_externe . ' -c ' . config::byKey('jeeNetwork::master::apikey') . ' -r ' . realpath(dirname(__FILE__));

		log::add('jeeserial', 'info', 'Lancement démon jeeSerial : ' . $cmd);
		$result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('jeeserial') . ' 2>&1 &');
		if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
			log::add('jeeserial', 'error', $result);
			return false;
		}
		sleep(2);
		if (!self::deamonRunning()) {
			sleep(10);
			if (!self::deamonRunning()) {
				log::add('jeeserial', 'error', 'Impossible de lancer le démon jeeSerial, vérifiez l\'ip', 'unableStartDeamon');
				return false;
			}
		}
		message::removeAll('jeeserial', 'unableStartDeamon');
		log::add('jeeserial', 'info', 'Démon jeeSerial lancé');
		end:
    }
    
    // Fonction qui lance le démon python dans le cas d'une installation locale
	public static function runDeamon() {
        log::add('jeeserial', 'info', 'Mode local');
        $jeeserial_path = realpath(dirname(__FILE__) . '/../../ressources');
		$modem_serie_addr = config::byKey('port', 'jeeserial');
		if($modem_serie_addr == "serie"){
			$port = config::byKey('modem_serie_addr', 'jeeserial');
			goto lancement;
		}
		$port = jeedom::getUsbMapping(config::byKey('port', 'jeeserial'));
        if (!file_exists($port)) {
			log::add('jeeserial', 'error', 'Le port n\'existe pas');
			goto end;
        }
		$cle_api = config::byKey('api');
		if($cle_api == ''){
			log::add('jeeserial', 'error', 'Erreur de clé api, veuillez la vérifier.');
			goto end;
		}	
		lancement:
		$cmd = 'nice -n 19 /usr/bin/python ' . $jeeserial_path . '/jeeSerial.py' . ' -p ' . $port . ' -c ' . config::byKey('api') . ' -r ' . realpath(dirname(__FILE__));
		log::add('jeeserial', 'info', 'Lancement démon jeeSerial : ' . $cmd);
		$result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('jeeserial') . ' 2>&1 &');
		if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
			log::add('jeeserial', 'error', $result);
			return false;
		}
		sleep(2);
		if (!self::deamonRunning()) {
			sleep(10);
			if (!self::deamonRunning()) {
				log::add('jeeserial', 'error', 'Impossible de lancer le démon jeeSerial, vérifiez l\'ip', 'unableStartDeamon');
				return false;
			}
		}
		message::removeAll('jeeserial', 'unableStartDeamon');
		log::add('jeeserial', 'info', 'Démon jeeSerial lancé');
		end:
    }
    
    // Test pour savoir si le démon est lancé
    public static function deamonRunning() {
		$result = exec("ps aux | grep jeeSerial.py | grep -v grep | awk '{print $2}'");
		if($result != ""){
			log::add('jeeserial', 'info', 'Démon jeeSerial lancé');
			return true;
		}
		log::add('jeeserial', 'info', 'Démon jeeSerial non lancé');
        return false;
    }

    // Arrête le démon
    public static function stopDeamon() {
        if (!self::deamonRunning()) {
            return true;
        }
        $result = exec("ps aux | grep jeeSerial.py | grep -v grep | awk '{print $2}'");
		//foreach ($result as $pid) {
			$resultKill = exec('kill ' . $result);
		//}
        $check = self::deamonRunning();
        $retry = 0;
        while ($check) {
            $check = self::deamonRunning();
            $retry++;
            if ($retry > 10) {
                $check = false;
            } else {
                sleep(1);
            }
        }
		try {
            $resultKill = exec('kill -9' . $result);
		} catch (Exception $e) {
			log::add('jeeserial', 'error', 'Impossible d\'arrêter le daemon jeeSerial');
		}
        $check = self::deamonRunning();
        $retry = 0;
        while ($check) {
            $check = self::deamonRunning();
            $retry++;
            if ($retry > 10) {
                $check = false;
            } else {
                sleep(1);
            }
        }
        return true;
    }
	
    /*     * *********************Methode d'instance************************* */

    /*public function forceUpdate() {
        foreach ($this->getCmd() as $cmd) {
            try {
                $cmd->forceUpdate();
            } catch (Exception $e) {
                
            }
        }
        try {
            //self::callTeleinfo('/teleinfo');
        } catch (Exception $e) {
            
        }
    }*/

}


// Commandes JeeSerial
class jeeserialCmd extends cmd {

    public function execute($_options = null) {
        // Execution d'une commande action
		if ($this->getType() == 'action') {
            // récupération du LogicalID de la commande
			$cmdLogicalId = $this->getLogicalId();
			
            // ajout d'un retour charriot
			$value = $cmdLogicalId . "\n";
			
            // ouverture du socket
			$socket = socket_create(AF_INET, SOCK_STREAM, 0);
			socket_connect($socket, '127.0.0.1', 55002);
			
            // envoie de la requête sur le socket
			socket_write($socket, $value, strlen($value));
            
            // fermueture du socket
			socket_close($socket);
		}
		
		log::add('jeeserial', 'info', 'Commande executée');
    }
    /*     * **********************Getteur Setteur*************************** */
}

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
 
 // Début CODE JeeDom OBLIGATOIRE DE SECURITE
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}

if (config::byKey('api') != '') {
	try {
		if($_GET["api"] != config::byKey('api')){
			if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
				if (config::byKey('api') != init('apikey')) {
					connection::failed();
					echo 'Clef API non valide, vous n\'etes pas autorisé à effectuer cette action (jeeApi)';
					log::add('jeeserial', 'error', 'Problème avec la clé API, modifiez la puis redémarrez le plugin');
					die();
				}
			}
		}
	} catch (Exception $e) {
        echo $e->getMessage();
        log::add('jeeserial', 'error', $e->getMessage());
    }
}
 // Fin CODE JeeDom OBLIGATOIRE DE SECURITE

 // Enregistrement de la requête dans les log
$array_recu = "";
foreach ($_GET as $key => $value){
    $array_recu = $array_recu . $key . $value . ' / ';
}
log::add('jeeserial', 'debug', 'Trame recu ' . $array_recu);


 // Récupération de la trame, et découpage si multi-requête
$trames = explode(",", $_GET['trame']);

 // Pour chaque requête
foreach($trames as $trame){
    // Découpage de la requête pour extraire les infos nécessaires au traitement
    $cmdLogicalId=substr($trame, 0 , 1); // Fonction (M, A, I, D, X...)
    $logicalId=substr($trame, 1, -1); // Adresse mémoire
    $value=substr($trame, -1); // Valeur
    
    if($cmdLogicalId == "X"){
        // S'il s'agit de la requête périodique, on ne garde que la fonction demandée
        $realCmdLogicalId = "X";
    }else{
        // Sinon, on colle la fonction à l'adresse mémoire: ça correspondra à la LogicalID de la commande
        $realCmdLogicalId = $cmdLogicalId . $logicalId;
    }

    // Recherche de la commande possédant le LogicalID demandé
    foreach(cmd::byLogicalId($cmdLogicalId . $logicalId, 'info') as $cmd){
        if($cmd->getEqLogic()->getEqType_name() == 'jeeserial'){
            // Si cette commande est bien rattaché à un équipement "jeeSerial", on en modifie la valeur
            $cmd->setValue($value);
            $cmd->event($value);
            log::add('jeeserial', 'event', 'Mise à jout de ' . $cmd->getHumanName() . ' terminée');
        }
    }
}
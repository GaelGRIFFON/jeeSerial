#!/usr/bin/python
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4
 
""" Interface avec le systeme domotiqe de Joel GOSSMANN
"""

# Import des librairies nécessaires
import serial
import os
import time
import traceback
import logging
import sys
from optparse import OptionParser
import subprocess
import urllib2
import socket
import threading
 
# Default log level
gLogLevel = logging.ERROR

###
# Paramètres par défaut:
#
# Interface série 
gDeviceName = '/dev/ttyUSB0'
# Sortie d'affichage
gOutput = sys.__stdout__
# En cas d'utilisation sur un module distant
gExternalIP = ''
gCleAPI = ''
gDebug = ''
gRealPath = ''
# Port du socket pour les requêtes entrantes
gSockPort = 55002

# ----------------------------------------------------------------------------
# LOGGING: 
# ----------------------------------------------------------------------------
class MyLogger:
	""" Our own logger """
	
	def __init__(self):
		program_path = os.path.dirname(os.path.realpath(__file__))
		self._logger = logging.getLogger('jeeSerial')
		hdlr = logging.FileHandler(program_path + '/jeeSerial.log')
		formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
		hdlr.setFormatter(formatter)
		self._logger.addHandler(hdlr) 
		self._logger.setLevel(gLogLevel)
	
	def debug(self, text):
		try:
			self._logger.debug(text)
		except NameError:
			pass
	
	def info(self, text):
		try:
			self._logger.info(text)
		except NameError:
			pass
	
	def error(self, text):
		try:
			self._logger.error(text)
		except NameError:
			pass
 
 
# ----------------------------------------------------------------------------
# Exception: en cas d'erreur
# ----------------------------------------------------------------------------
class JeeSerialException(Exception):
	"""
	jeeSerial exception
	"""
	
	def __init__(self, value):
		Exception.__init__(self)
		self.value = value
	
	def __str__(self):
		return repr(self.value)
 
 
# ----------------------------------------------------------------------------
# jeeSerial core
# ----------------------------------------------------------------------------
class JeeSerial:
	""" Classe principale du démon d'interface
	"""
	
	def __init__(self, device, externalip, cleapi, debug, realpath, sockport):
		"""
		Initialisation de la classe
		@param device : jeeSerial modem device path
		"""
		# Enregistrement des paramètres en interne
		self._log = MyLogger()
		self._device = device
		self._externalip = externalip
		self._cleAPI = cleapi
		self._debug = debug
		self._realpath = realpath
		self._sockport = sockport
		self._ser = None
		self._sock = None
		self._write_lock = threading.Lock()
		self.alive = False
	
	def openSerial(self):
		""" Ouverture du modem série
		"""
		try:
			self._log.info("Essai d'ouverture du modem série '%s'" % self._device)
			self._ser = serial.Serial(self._device, 9600, bytesize=8, parity = 'N', stopbits=1)
			self._log.info("Ouverture du modem série ouvert avec succès")
		except:
			error = "Erreur d'ouverture du modem série '%s' : %s" % (self._device, traceback.format_exc())
			raise JeeSerialException(error)
	
	def openSocket(self):
		""" Ouverture du socket pour l'écoute
		"""
		try:
			self._log.info("Essai d'ouverture du socket")
			self._sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
			self._sock.bind( ('', self._sockport ) )
			self._sock.listen(1)
			self._log.info("Ouverture du socket avec succès")
		except:
			error = "Erreur d'ouverture du socket : %s" % (traceback.format_exc())
			raise JeeSerialException(error)
	
	def closeSerial(self):
		""" Fermeture Serial
		"""
		if self._ser != None  and self._ser.isOpen():
			self._ser.close()
	
	def closeSocket(self):
		""" Fermeture Socket
		"""
		if self._sock != None :
			self._sock.close()
	
	def terminate(self):
		""" Fin du programme
		"""
		print "Terminating..."
		# Fermeture du port série et du socket
		self.closeSerial()
		self.closeSocket()
		# Sortie du programme
		sys.exit(0)
	
	def readFromSerial(self):
		# Lecture du port série
		
		# Ecoute en permanence
		while(1):
			_SendData = ""
			
			###############################"
			# Lecture d'une trame
			trame = self._ser.readline() # On attend un retour charriot
			trame = trame[0:-1] # On enlève le dernier caractère (retour charriot)
			
			###############################"
			# Envoie de l'info vers JeeDom
			if(self._externalip != ""):
				# Si on est sur un système distant
				self.cmd = 'http://' + self._externalip +'/plugins/jeeSerial/core/php/jeeserial.php?api=' + self._cleAPI
				_Separateur = "&"
			else:
				# Si on est directement sur le serveur Jeedom (requête en local)
				self.cmd = 'nice -n 19 /usr/bin/php ' + self._realpath + '/../php/jeeserial.php api=' + self._cleAPI
				_Separateur = " "
				
			# Construction des données à envoyer
			if(trame != ""):
				_SendData += _Separateur + 'trame=' + trame
			
			
			if (_SendData != ""):
				self.cmd += _SendData
				if (self._debug == '1'):
					print self.cmd
				if(self._externalip != ""):
					try:
						# Si on est à distance: on fait une requête HTTP via l'URL construite
						response = urllib2.urlopen(self.cmd)
					except Exception, e:
						errorCom = "Connection error '%s'" % e
				else:
					# Si on est en local: on lance la commande construite
					self.process = subprocess.Popen(self.cmd, shell=True)
					self.process.communicate()
	
	def writeFromSocket(self):
		# Ecoute en continue du socket pour retransmettre sur le port Série
		while True:
			try:
				sys.stderr.write("Waiting for connection on %s...\n" % self._sockport)
				# Bloque ici jusqu'à avoir une connexion entrante
				connection, addr = self._sock.accept()
				sys.stderr.write('Connected by %s\n' % (addr,))				
			except KeyboardInterrupt:
				break
			except socket.error, msg:
				error = "Erreur sur le socket: %s" % msg
				raise JeeSerialException(error)
			
			# Lorsqu'une connexion entrante est établie, on attend les données
			while True:
				try:
					data = connection.recv(1024)
					# Lorsque des données sont reçues...
					if not data:
						break
					
					if (self._debug == '1'):
						sys.stderr.write(data)
					
					# ... On acquiert le doit d'écrire sur le port série
					self._write_lock.acquire()
					try:
						# Puis on écrit les données reçues du socket
						self._ser.write(data)
					finally:
						# Puis on libère le droit d'écriture
						self._write_lock.release()
				
				except socket.error, msg:
					sys.stderr.write('ERROR: %s\n' % msg)
					# probably got disconnected
					break
			
			# Lorsque la connexion se termine, elle est fermée puis on reboucle en attendant une nouvelle connexion
			sys.stderr.write('Disconnected\n')
			connection.close()
	
	def checkAlive(self):
		# Envoie d'une requête (X) périodique (toutes les 30 sec) pour vérifier la réponse du périphérique distant
		while True:
			self._write_lock.acquire()
			try:
				self._ser.write("X\n")
			finally:
				self._write_lock.release()
			
			time.sleep(30)
			
	def run(self):
		""" Main function
		"""
		# Ouverture du modem
		try:
			self.openSerial()
		except JeeSerialException as err:
			self._log.error(err.value)
			#print err.value
			self.terminate()
			return
		
		# Ouverture du socket
		try:
			self.openSocket()
		except JeeSerialException as err:
			self._log.error(err.value)
			#print err.value
			self.terminate()
			return
		
		###########
		# Lancement des thread de lecture et d'écriture
		# (nota: les Threads permettent d'écouter à la fois le port série et le socket en parallèle sans bloquer le programme)
		###########
		
		# Démon d'écoute du port série
		self.thread_read = threading.Thread(target=self.readFromSerial)
		self.thread_read.setDaemon(True)
		self.thread_read.setName('serial->jeedom')
		self.thread_read.start()
		
		# Démon de vérification périodique du lien
		self.thread_checkAlive = threading.Thread(target=self.checkAlive)
		self.thread_checkAlive.setDaemon(True)
		self.thread_checkAlive.setName('serialCheckAlive')
		self.thread_checkAlive.start()
		
		# Envoie de la demande de DUMP à l'ouverture
		# acquisition du droit d'écriture sur le port série
		self._write_lock.acquire()
		try:
			# envoie de la requête S
			self._ser.write("S\n")
		finally:
			self._write_lock.release()
		
		# Ecoute du socket
		self.writeFromSocket()
		
		# This is the End!
		self.terminate()
	
#------------------------------------------------------------------------------
# MAIN: éxécuté au lancement du programme
#------------------------------------------------------------------------------
if __name__ == "__main__":
	# Définition des options du programme
	usage = "usage: %prog [options]"
	parser = OptionParser(usage)
	parser.add_option("-o", "--output", dest="filename", help="append result in FILENAME")
	parser.add_option("-p", "--port", dest="port", help="port du modem")
	parser.add_option("-e", "--externalip", dest="externalip", help="ip de jeedom")
	parser.add_option("-c", "--cleapi", dest="cleapi", help="clé api de jeedom")
	parser.add_option("-d", "--debug", dest="debug", help="mode debug")
	parser.add_option("-r", "--realpath", dest="realpath", help="path usr")
	parser.add_option("-s", "--socket", dest="sock", help="socket port")
	(options, args) = parser.parse_args()
	
	# Enregistrement des paramètres passés
	if options.port:
			try:
				gDeviceName = options.port
			except:
				error = "Impossible de changer le port %s" % options.port
				raise TeleinfoException(error)
	if options.externalip:
			try:
				gExternalIP = options.externalip
			except:
				error = "Impossible de changer l'ip %s" % options.externalip
				raise TeleinfoException(error)
	if options.debug:
			try:
				gDebug = options.debug
			except:
				error = "Impossible de se mettre en mode débug %s" % options.debug
				#raise TeleinfoException(error)
	if options.cleapi:
			try:
				gCleAPI = options.cleapi
			except:
				error = "Impossible de changer la clé API %s" % options.cleapi
				raise TeleinfoException(error)
	if options.realpath:
			try:
				gRealPath = options.realpath
			except:
				error = "Impossible de changer le chemin réel %s" % options.realpath
				raise TeleinfoException(error)
	
	if options.sock:
			try:
				gSockPort = int(options.sock)
			except:
				error = "Impossible de changer port du socket %s" % options.sock
				raise TeleinfoException(error)
	
	# Création d'une instance de JeeSerial avec les paramètres passés
	jeeSerial = JeeSerial(gDeviceName, gExternalIP, gCleAPI, gDebug, gRealPath, gSockPort)
	# Lancement du programme principale de l'instance
	jeeSerial.run()
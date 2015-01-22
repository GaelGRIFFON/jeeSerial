# Importation des modules nécessaires
import serial
import os

# Port série (à éventuellement modifier)
device = "COM4"

# Ouveture du port série
print("Essai d'ouverture du modem série '%s'" % device)
portSerie = serial.Serial(device, 9600, bytesize=8, parity = 'N', stopbits=1)
print("Ouverture du modem série ouvert avec succès")

# écoute en permanence
while True:
	# Bloqué ici en attendant un retour charriot
	ligne = portSerie.readline()
	
	# découpage de la ligne
	fonction = ligne[0]
	adresse = ligne[1:-1]
	
	# Traitement de la requête
	print "------------------------"
	print " Fonction: " + fonction 
	print " Adresse:  " + adresse
	
	if fonction == "M":
		reponse = "E" + str(int(adresse)-20) + "1"
	
	if fonction == "A":
		reponse = "E" + str(int(adresse)-30) + "0"
	
	if fonction == "I":
		reponse = "F" + str(int(adresse)-30) + "1"
	
	if fonction == "D":
		reponse = "F" + str(int(adresse)-40) + "0"
	
	if fonction == "X":
		reponse = "X"
	
	if fonction == "S":
		reponse = "E0011,E0020,E0031,E0040,E0051,E0060,E0071,E0080,E0091,E0100,E0111,E0120,E0130,E0141"
		
	print " Reponse:  " + reponse
	
	# renvoie de la réponse en fonction de la requête reçue
	writtenBite = portSerie.write(reponse + "\n")
	
	print ""

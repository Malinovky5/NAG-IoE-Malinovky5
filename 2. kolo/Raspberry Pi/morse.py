#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys, os, requests
import RPi.GPIO as GPIO
import pyDisplay
from time import sleep

Alphabet = {
	"A" : ".-",
	"B" : "-...",
	"C" : "-.-.",
	"D" : "-..",
	"E" : ".",
	"F" : "..-.",
	"G" : "--.",
	"H" : "....",
	"I" : "..",
	"J" : ".---",
	"K" : "-.-",
	"L" : ".-..",
	"M" : "--",
	"N" : "-.",
	"O" : "---",
	"P" : ".--.",
	"Q" : "--.-",
	"R" : ".-.",
	"S" : "...",
	"T" : "-",
	"U" : "..-",
	"V" : "...-",
	"W" : ".--",
	"X" : "-..-",
	"Y" : "-.--",
	"Z" : "--..",
	" " : "/",
	"1" : ".----",
	"2" : "..---",
	"3" : "...--",
	"4" : "....-",
	"5" : ".....",
	"6" : "-....",
	"7" : "--...",
	"8" : "---..",
	"9" : "----.",
	"0" : "-----",
	"." : ".-.-.-",
	":" : "---..."
}

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.setup(21, GPIO.OUT)
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)

decodedString = ''
request = requests.get('https://ioe.zcu.cz/morse.php?id=pU102hrEkVMMRwWhJI8AZe2IxZPtgRHY')
encodedText = request.text

for letter in encodedText.split(" "):

	for value in Alphabet.items():
		if(letter == value[1]):
			decodedString += value[0]
			pass

print(decodedString)

try:
	while True:
		if GPIO.input(18): 
			pass 
		else: 
			for boolText in encodedText:
				if boolText == ".":
					#print("Tečka!")
					GPIO.output(21,GPIO.HIGH)
					sleep(0.2)
					GPIO.output(21,GPIO.LOW)
					sleep(0.2)

				if boolText == "-":
					#print('Čárka!')
					GPIO.output(21,GPIO.HIGH)
					sleep(0.5)
					GPIO.output(21,GPIO.LOW)
					sleep(0.5)

				if boolText == "/":
					#print('Mezera!')
					GPIO.output(21,GPIO.LOW)
					sleep(1)
					GPIO.output(21,GPIO.LOW)
					sleep(1)

			sleep(0.2)

except KeyboardInterrupt:
	GPIO.cleanup()
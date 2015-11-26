#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import subprocess, sys, os, platform, requests
import RPi.GPIO as GPIO
import pyDisplay
from time import sleep

# try:
# 	cpuSerial = subprocess.check_output("cat /proc/cpuinfo | grep Serial | cut -d ':' -f 2 | tr -s '\n' | cut -d ' ' -f 2", shell=True)
# 	print(cpuSerial);
# except Exception as e:
# 	print('Chyba: ' + str(e))

cpuSerial = '0000000000000000'
try:
	f = open('/proc/cpuinfo','r')
	for line in f:
		if line[0:6] == 'Serial':
			cpuSerial = line[10:26]
	f.close()

	if cpuSerial == '0000000000000000':
		print('Error!')
		exit()
except Exception as e:
	print('Chyba: ' + str(e))
	exit()

try:
	hostname = platform.uname()[1]
except Exception as e:
	print('Chyba: ' + str(e))
	exit()

jmenoTymu = 'Malinovky5'

#print( 'CPU: ' + cpuSerial)
#print('Hostname: ' + hostname)
#print('Jméno týmu: ' + jmenoTymu)
#print('Posílám data na server...')

lcd = pyDisplay.lcd(0x3f, 1)
#lcd = pyDisplay.lcd(0x3f, 1)
lcd.lcd_write(0x01)
lcd.lcd_clear()

GPIO.setmode(GPIO.BCM) # Broadcom pin-numbering scheme
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP) # Button pin set as input w/ pull-up

try:
	while True:
		if GPIO.input(18): 
			pass
		else: 
			lcd.lcd_clear()
			lcd.lcd_puts("Vitejte!",1)
			sleep(1)
			lcd.lcd_puts("Jsme Malinovky", 2)
			sleep(2)
			lcd.lcd_clear()
			lcd.lcd_puts("CPU:", 1)
			sleep(2)
			lcd.lcd_clear()
			lcd.lcd_puts(cpuSerial, 1)
			sleep(2)
			lcd.lcd_clear()
			lcd.lcd_puts("Hostname: ", 1)
			sleep(2)
			lcd.lcd_clear()
			lcd.lcd_puts(hostname, 1)
			sleep(2)
			lcd.lcd_clear()
			lcd.lcd_puts("Posilam...", 1)

			payload_2 = {'cpuid': cpuSerial, 'name': jmenoTymu, 'hostname': hostname}
			request_2 = requests.get('https://ioe.zcu.cz/update.php?id=pU102hrEkVMMRwWhJI8AZe2IxZPtgRHY', params=payload_2)

			payload = {'cpuid': cpuSerial, 'name': jmenoTymu, 'hostname': hostname}
			request = requests.get('https://ioe.zcu.cz/getinfo.php?id=pU102hrEkVMMRwWhJI8AZe2IxZPtgRHY', params=payload)
			print(request.status_code)
			print(request.text)

			lcd.lcd_clear()

			if(request.status_code == 200):
				request.status_code = str(request.status_code) + ' OK'
				isOk = 'Data v poradku'
			else:
				request.status_code = str(request.status_code) + ' Error'
				isOk = 'Neco nefunguje'

			sleep(1)

			lcd.lcd_puts(request.status_code, 1)
			lcd.lcd_puts(isOk, 2)
		sleep(0.4)

except KeyboardInterrupt:
	GPIO.cleanup()

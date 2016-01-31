#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import subprocess, sys, os, requests, json
import pyDisplay
import serial
import time

lcd = pyDisplay.lcd(0x3f, 1)
lcd.lcd_write(0x01)
lcd.lcd_write(0x01)
lcd.lcd_puts('Booting', 1)

ser = serial.Serial('/dev/ttyUSB0', 9600, timeout = 1, parity=serial.PARITY_NONE, stopbits=serial.STOPBITS_ONE, bytesize=serial.EIGHTBITS)
time.sleep(1) # cekani nez se seriova linka inicializuje

count = 0
last_temp = ''
last_humidity = ''

while True:
	data = str(ser.readline())
	if(count >= 2):
		data = data[2:-5]
		file = open('/var/www/hodnoty.json','w') 
		file.write(data)
		file.close()
		count = 1

		try:
			data_json = json.loads(data)
			payload = {'id': 'pU102hrEkVMMRwWhJI8AZe2IxZPtgRHY', 'temperature': data_json['Temperature'], 'humidity': data_json['Humidity']}
			requests.get('https://ioe.zcu.cz/th.php', params=payload)
			lcd.lcd_clear()
			lcd.lcd_puts('Teplota:', 1)
			lcd.lcd_puts(str(data_json['Temperature']), 2)
			time.sleep(0.5)
			lcd.lcd_clear()
			lcd.lcd_puts('Vlhkost:', 1)
			lcd.lcd_puts(str(data_json['Humidity']), 2)

			#print(time.strftime('%A'))

			if(data_json['Temperature'] != last_temp):
				file_write = open('/var/www/days/temp/' + time.strftime('%A') + '.txt', 'a', encoding='utf-8')
				file_write.write(data_json['Temperature'] + ',')
				file_write.close()
				last_temp = data_json['Temperature']

			if(data_json['Humidity'] != last_humidity):
				file_write = open('/var/www/days/humidity/' + time.strftime('%A') + '.txt', 'a', encoding='utf-8')
				file_write.write(data_json['Humidity'] + ',')
				file_write.close()
				last_humidity = data_json['Humidity']

			file_date_read = open('/var/www/days/date.txt', 'r')
			if(int(time.strftime('%j')) >= int(file_date_read.read())):
				file_date = open('/var/www/days/date.txt','w')
				file_date.write(str(int(time.strftime('%j')) + 7))
				file_date.close()

				os.system('echo -n "" > /var/www/days/temp/Monday.txt')
				os.system('echo -n "" > /var/www/days/temp/Tuesday.txt')
				os.system('echo -n "" > /var/www/days/temp/Wednesday.txt')
				os.system('echo -n "" > /var/www/days/temp/Thursday.txt')
				os.system('echo -n "" > /var/www/days/temp/Friday.txt')
				os.system('echo -n "" > /var/www/days/temp/Saturday.txt')
				os.system('echo -n "" > /var/www/days/temp/Sunday.txt')

				os.system('echo -n "" > /var/www/days/humidity/Monday.txt')
				os.system('echo -n "" > /var/www/days/humidity/Tuesday.txt')
				os.system('echo -n "" > /var/www/days/humidity/Wednesday.txt')
				os.system('echo -n "" > /var/www/days/humidity/Thursday.txt')
				os.system('echo -n "" > /var/www/days/humidity/Friday.txt')
				os.system('echo -n "" > /var/www/days/humidity/Saturday.txt')
				os.system('echo -n "" > /var/www/days/humidity/Sunday.txt')

				print('Jede!')

		except:
			pass

	count += 1
	time.sleep(0.5)


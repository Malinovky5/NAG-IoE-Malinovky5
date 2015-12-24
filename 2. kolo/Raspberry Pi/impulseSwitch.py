#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys, os
import RPi.GPIO as GPIO
from time import sleep

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.setup(21, GPIO.OUT)
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)

status = False

try:
	while True:
		if GPIO.input(18): 
			pass 
		else: 
			if status == False:
				status = True
				GPIO.output(21,GPIO.HIGH)
			else:
				status = False
				GPIO.output(21,GPIO.LOW)
							
		sleep(0.2)

except KeyboardInterrupt:
	GPIO.cleanup()
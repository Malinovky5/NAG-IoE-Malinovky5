#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys, os
import RPi.GPIO as GPIO
from time import sleep

GPIO.setmode(GPIO.BCM)
GPIO.setup(21, GPIO.OUT)
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)

try:
	while True:
		if GPIO.input(18): 
			GPIO.output(21,GPIO.LOW)  
		else: 
			GPIO.output(21,GPIO.HIGH)  
		sleep(0.2)

except KeyboardInterrupt:
	GPIO.cleanup()
#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys, os
import RPi.GPIO as GPIO
from time import sleep

def blink(pin):  
        GPIO.output(pin,GPIO.HIGH)  
        sleep(0.3)  
        GPIO.output(pin,GPIO.LOW)  
        sleep(0.3)  
        return  
  
GPIO.setmode(GPIO.BCM)  
GPIO.setup(21, GPIO.OUT)  

for i in range(0,50):  
        blink(21)  

GPIO.cleanup()   
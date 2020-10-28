<?php

const EVENTS_NUMBER = 10000;
const ACCOUNTS_NUMBER = 1000;
const LIMIT_EVENT_ON_ACCOUNT = 10;

$generator = new EventsGenerator(EVENTS_NUMBER, ACCOUNTS_NUMBER, LIMIT_EVENT_ON_ACCOUNT);
$generator->generate();

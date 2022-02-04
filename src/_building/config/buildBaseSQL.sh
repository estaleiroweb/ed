#!/bin/bash

mysqldump -E --opt --result-file=base.sql -R --triggers --databases db_Secure db_Secure_Logs db_System 

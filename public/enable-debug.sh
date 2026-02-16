#!/bin/bash
# Enable debug mode
cp .htaccess .htaccess.backup
cp .htaccess.debug .htaccess
echo "Debug mode enabled!"
echo "To disable: cp .htaccess.backup .htaccess"

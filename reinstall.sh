 sudo docker-compose stop \
 && sudo docker-compose rm -f \
 && sudo rm -r mysql/* wordpress/* \
 && sudo docker-compose up -d
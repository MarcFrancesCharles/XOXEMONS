FROM node:20

WORKDIR /app

# Instal·lem Angular CLI globalment dins del contenidor
RUN npm install -g @angular/cli

# Aixequem el servidor d'Angular i el fem visible cap a fora (0.0.0.0)
CMD sh -c "npm install && ng serve --host 0.0.0.0 --poll 2000"
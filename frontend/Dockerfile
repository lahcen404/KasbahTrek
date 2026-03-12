FROM node:20-alpine

WORKDIR /app

ENV NG_CLI_ANALYTICS=false

RUN npm install -g @angular/cli@18

COPY package.json package-lock.json* ./

RUN npm ci --legacy-peer-deps || npm install --legacy-peer-deps

COPY . .

EXPOSE 4200

CMD ["sh", "-c", "npm install --legacy-peer-deps && ng serve --host 0.0.0.0 --port 4200 --proxy-config proxy.docker.conf.json --disable-host-check --poll=2000"]

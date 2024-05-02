
FROM node:20-alpine
WORKDIR /usr/src/app
RUN apk add --update alpine-sdk && apk add linux-headers
RUN apk add --update --no-cache python3 && ln -sf python3 /usr/bin/python
# install pip
RUN python3 -m ensurepip

COPY package.json ./

RUN npm i

COPY . .

RUN npm run build

ENV NODE_ENV production
CMD [ "node", "dist/index.js" ]
USER node
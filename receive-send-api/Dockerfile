FROM node:20-alpine

WORKDIR /app

COPY package.json /app/
RUN npm install 

COPY src/ /app/src/

EXPOSE 8002

CMD ["npm", "start"]

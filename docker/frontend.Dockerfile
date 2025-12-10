FROM node:20

WORKDIR /app

RUN npm install -g pnpm

COPY package.json pnpm-lock.yaml* ./
RUN pnpm install

COPY . .

EXPOSE 8083

CMD ["pnpm", "run", "dev", "--host", "0.0.0.0", "--port", "8083"]

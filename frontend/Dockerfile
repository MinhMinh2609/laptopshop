# Stage 1: Build Vue.js app
FROM node:20-alpine AS builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

# VITE_* vars are baked in at build time — set them via docker-compose build args
ARG VITE_API_URL=http://localhost:8000/api
ARG VITE_BACKEND_URL=http://localhost:8000
ENV VITE_API_URL=$VITE_API_URL
ENV VITE_BACKEND_URL=$VITE_BACKEND_URL

COPY . .
RUN npm run build

# Stage 2: Serve static files with Nginx
FROM nginx:1.25-alpine

COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]

FROM nginx:1.25-alpine

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Bake backend/public/ vào image để nginx có thể serve
# (không cần bind mount từ host)
COPY backend/public /var/www/backend/public

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]

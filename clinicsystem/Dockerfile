    # Use the official lightweight Node.js 14 image as a base
    FROM node:14-alpine

    # Set working directory
    WORKDIR /app

    # Copy package.json and package-lock.json
    COPY package*.json ./

    # Install dependencies
    RUN npm install

    # Copy the rest of the application
    COPY . .

    # Set the environment variable
   ENV COMPOSER_ALLOW_SUPERUSER 1
    # Build the application
    RUN npm run build

    # Expose port
    EXPOSE 8080

    # Run the application
    CMD ["npm", "run", "serve"]

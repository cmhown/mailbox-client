FROM node:18-alpine

# Set the working directory
WORKDIR /app

# Copy the app code
COPY . .

# Copy .env file
WORKDIR /app

# Install dependencies
RUN npm install

# Start the React app
CMD ["npm", "start"]

# Expose the development server port
EXPOSE 3000

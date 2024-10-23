# Blog CRUD API JWT Application

![apiplatform](https://github.com/user-attachments/assets/c141ca2f-7dd1-48fa-9df7-ad3da71db8cc)

This Blog CRUD API JWT Application is built with Symfony. It provides a REST API for managing blog posts with authentication using JWT. This document outlines the steps required to set up the API on your local development environment.

## Prerequisites

Before proceeding with the setup, ensure you have the following installed on your machine:

-   Docker
-   Docker Compose

## Setup

#### 1.  **Clone the Repository**
    
First, clone the repository to your local machine. Open a terminal and run the following command:
    
`git clone git@github.com:gediminasnn/symfony.blog-crud-api-jwt.git` 
    
(Optional) Replace `git@github.com:gediminasnn/symfony.blog-crud-api-jwt.git` with the URL of repository.
    
#### 2.  **Navigate to the Application Directory**
    
Change directory to the application root:
    
`cd symfony.blog-crud-api-jwt` 
    
(Optional) Replace `symfony.symfony.blog-crud-api-jwt` with the path where you cloned the repository.
    
#### 3.  **Prepare the Environment File**
    
Prepare the application's environment file. Locate the `.env.example` file in the application root and create a new file named `.env` using it as a template. Optionally, edit the `.env` file to adjust any environment variables specific to your setup.

####  4.  **Start the Docker Containers**
    
Use Docker Compose to start the Docker containers. Run the following command in your terminal:
    
`docker-compose up`
    
This command builds and starts all containers needed for the application. The first time you run this, it might take a few minutes to download and build everything.

####  5.  **Generate JWT SSL Keys**
    
Generate SSL keys for JWT authentication:
    
`docker-compose exec php bin/console lexik:jwt:generate-keypair`
    
The keys will be created at `config/jwt/private.pem` and `config/jwt/public.pem`.

#### 6. **Run Database Seeds**

After successfully running the migrations, it's time to populate the database with some initial data, run the database fixtures. Ensure your Docker containers are up and running. In the terminal, execute the following command:

`docker-compose exec php bin/console doctrine:fixtures:load`

This command will execute the fixtures defined in your application, populating the database with sample or default data.

#### 7. **(Optional) Create Test Database**

To set up the test database, execute the following commands:

`docker-compose exec php bin/console doctrine:database:create --env=test`

and

`docker-compose exec php bin/console doctrine:migrations:migrate --env=test`

This command creates the necessary database for running tests.    

#### 7.  **(Optional) Run Tests**
    
Ensure that your Docker containers are still up and running. Open a new terminal window or tab and execute the following command:
    
`docker-compose exec php bin/phpunit` 
    
This command will use phpunit's built-in test runner to execute your application's test suite. It will run all the tests located in the tests directory of your application.

By completing this step, you will have fully set up your Blog CRUD API JWT Application on your local development environment, ensuring it is ready for further development, testing, or deployment. The API platform sits at `https://localhost/api/`, where you can access detailed documentation and interact with the available endpoints. There is also a preloaded user by fixtures with the credentials: username `username123` and password `password123`.

## API Documentation

You can send HTTP requests to the following RESTful endpoints:

1. Get JWT token
    ```
    POST /api/login
    Content-Type: application/json

    {
      "username": "username123",
	  "password": "password123"
    }
    ``` 

    ```
    HTTP/1.1 200 OK
    Content-Type: application/json
    
    {
	  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3Mjk2MzYzNDUsImV4cCI6MTcyOTYzOTk0NSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidXNlcm5hbWUxMjMifQ.NxEY_R6K_j4yEogJHSOLjG6t8XWM3-XQhqYjx1xUQSVKCOP3NIR_Z7zvEvP3Hw6szAtQ39plQ64xAE1mXRp6jginaaujNwVo8FmpMDbnyR8O-uF6f4bB4ZZWwaejBKBaPeykudXt7fNheoP7ebiEVKv-V7lpxzfLNs3femN20DwKuxsLx6qRH-CA3V-vG4UdVDJc8GAA6foShLa4KfuaZ8g7sDh7-XLWPW74G9lc60uYsGO-ISg8IiUQ8dp5F3WC9tShvoxF2NcHGrYtrBF0VVf43SI4nepDb_he8Ecqf97AZvGZ_sJSZajvvc7h1x0Xbjl4kTR8g0ltx8qua_0rGg"
	}
    ```
 
2. Retrieves the collection of Post resources.
    ```
    GET /api/posts/
    Authorization: Bearer {{JWTTOKEN}}
    ``` 

    ```
    HTTP/1.1 200 OK
    Content-Type: application/ld+json
    
    {
	  "@context": "/api/contexts/Post",
	  "@id": "/api/posts",
	  "@type": "Collection",
	  "totalItems": 100,
	  "member": [
	    {
	      "@id": "/api/posts/1",
	      "@type": "Post",
	      "id": 1,
	      "title": "Est autem sed ad animi consequuntur est.",
	      "content": "Pariatur ex deserunt nostrum natus ea nostrum qui excepturi. Id assumenda quia non est quibusdam autem perspiciatis. Ut eveniet omnis asperiores eligendi quod ut.",
	      "timestamp": "2024-10-24T16:54:23+00:00"
	    },
	    {
	      "@id": "/api/posts/2",
	      "@type": "Post",
	      "id": 2,
	      "title": "Fugiat harum eveniet iure adipisci esse.",
	      "content": "Sit eligendi ducimus eum rerum. Officiis eveniet dolor facilis dolore voluptatem et neque vero. Officiis aut et aliquid eos est deleniti animi.",
	      "timestamp": "2024-10-24T16:54:23+00:00"
	    },
	    ...
	  ],
	  "view": {
	    "@id": "/api/posts?page=1",
	    "@type": "PartialCollectionView",
	    "first": "/api/posts?page=1",
	    "last": "/api/posts?page=4",
	    "next": "/api/posts?page=2"
	  }
	}
    ```

3. Creates a Post resource
    ```
    POST /api/posts/
    Authorization: Bearer {{JWTTOKEN}}
    Content-Type: application/ld+json
    
	{
	  "title": "testTitle",
	  "content": "testContent"
	}
    ``` 

    ```
    HTTP/1.1 201 Created
    Content-Type: application/ld+json
    
    {
	  "@context": "/api/contexts/Post",
	  "@id": "/api/posts/101",
	  "@type": "Post",
	  "id": 101,
	  "title": "testTitle",
	  "content": "testContent",
	  "timestamp": "2024-10-24T16:56:30+00:00"
	}
    ```

4. Retrieves a Post resource
    ```
    GET /api/posts/{id}
    ``` 

    ```
    HTTP/1.1 200 OK
    Content-Type: application/ld+json
    
    {
	  "@context": "/api/contexts/Post",
	  "@id": "/api/posts/1",
	  "@type": "Post",
	  "id": 1,
	  "title": "Est autem sed ad animi consequuntur est.",
	  "content": "Pariatur ex deserunt nostrum natus ea nostrum qui excepturi. Id assumenda quia non est quibusdam autem perspiciatis. Ut eveniet omnis asperiores eligendi quod ut.",
	  "timestamp": "2024-10-24T16:54:23+00:00"
	}
    ```

5. Updates the Post resource
    ```
    PATCH /api/posts/{id}
    Authorization: Bearer {{JWTTOKEN}}
    Content-Type: application/merge-patch+json

    {
	  "title": "testTitle2",
	}
    ``` 

    ```
    HTTP/1.1 200 OK
    Content-Type: application/ld+json
    
    {
	  "@context": "/api/contexts/Post",
	  "@id": "/api/posts/101",
	  "@type": "Post",
	  "id": 101,
	  "title": "testTitle2",
	  "content": "testContent",
	  "timestamp": "2024-10-24T16:58:30+00:00"
	}
    ```

6. Removes the Post resource
    ```
    DELETE /api/posts/{id}
    Authorization: Bearer {{JWTTOKEN}}
    ``` 

    ```
    HTTP/1.1 204 No Content   
    ```
## License

This project is licensed under the MIT License

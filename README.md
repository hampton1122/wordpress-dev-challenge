## Wordpress Developer Demo
A simple apache/php wordpress app dockerized for local development to test a developers skills in php and wordpress.

# RUN
- In directory, run ```docker build --rm -f "Dockerfile" -t wordpress:latest .```
- Then run ```docker-compose up```
- In your browser, visit ```http://localhost:8000``` to complete the Wordpress installation. Your docker-compose with have the database credentials and host.

# TASK
- Create a plugin that allows the admin to search an api and return results in a table displayed in the WP Admin Plugin page
    - API Endpoint: http://jservice.io/api/clues
- Each result should have a save button that creates a post with the following data saved:
    - Post Title = item.question
    - Post Content = item.answer
    - Post Type = jeopardy
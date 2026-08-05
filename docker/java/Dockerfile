FROM openjdk:17-slim

WORKDIR /app

COPY docker/java/JavaDeserializerServer.java /app/JavaDeserializerServer.java

RUN javac /app/JavaDeserializerServer.java

EXPOSE 8088

CMD ["java", "-cp", "/app", "JavaDeserializerServer"]

-- schema.sql - run this in MySQL to create database and table

CREATE DATABASE IF NOT EXISTS student_crud CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_crud;

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  course VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

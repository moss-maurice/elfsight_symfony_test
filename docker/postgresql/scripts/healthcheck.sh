#!/bin/sh

# Проверка готовности PostgreSQL
pg_isready -U pgsql_user -d pgsql_db

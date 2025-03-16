FROM python:3.8
ENV TZ="Europe/Samara"
WORKDIR /ytapi
COPY requirements.txt /ytapi
RUN pip3 install --upgrade pip -r requirements.txt
COPY . /ytapi
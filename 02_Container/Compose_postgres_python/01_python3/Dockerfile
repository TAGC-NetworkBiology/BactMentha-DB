# ---------------------------------------------------------------------------
# Dockerfile for building image bactmentha_python3 for the BactMentha Project
# ---------------------------------------------------------------------------

# ----------
# Base image
# ----------

FROM python:3.9 


# -------------------------------------------
# installing postgres dependencies for python
# -------------------------------------------

RUN apt-get update \
  && apt-get install -y postgresql postgresql-contrib \
  && apt-get install sudo \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


# --------------
# installing pip
# --------------

RUN apt-get update && apt-get install -y python3-pip


# ----------------------------------------------------------------------
# installing python librairies used for the database creation and update
# ----------------------------------------------------------------------

RUN pip install --upgrade pip
RUN pip3 install psycopg2
RUN pip3 install plotly
RUN pip3 install -U kaleido
RUN pip3 install datetime
RUN pip3 install pandas
RUN pip3 install numpy

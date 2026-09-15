# ---------------------------------------------------------------------------
# Dockerfile for building image bactmentha_python3 for the BactMentha Project
# ---------------------------------------------------------------------------

# ----------
# Base image
# ----------

FROM python:3.11


# -------------------------------------------
# installing postgres dependencies for python
# -------------------------------------------

RUN apt-get update && \
    apt-get install -y \
        postgresql \
        postgresql-contrib \
        sudo \
        git \
        libpq-dev \
        gcc && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


# --------------
# installing pip
# --------------

RUN apt-get update && apt-get install -y python3-pip


# ----------------------------------------------------------------------
# installing python librairies used for the database creation and update
# ----------------------------------------------------------------------


RUN pip install --upgrade pip && \
    pip install \
        psycopg2 \
        plotly \
        -U kaleido \
        pandas \
        numpy \
        biopython \
        git+https://github.com/evo-design/proto-tools.git


# ------------------------------------
# Getting Interactome3Dcontacts script
# ------------------------------------

COPY tools/residue_contacts_Interactome3D /usr/local/bin/residue_contacts_Interactome3D
RUN chmod +x /usr/local/bin/residue_contacts_Interactome3D


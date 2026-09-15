#!/bin/bash

pdb_file="$1"
out_file="$2"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INTERACTOME3D="$SCRIPT_DIR/residue_contacts_Interactome3D"

# Run Interactome3D and save the complete output to a temporary file
"$INTERACTOME3D" -t -C "$pdb_file" A B > "${out_file}.tmp"

# Add the number of unique contact residues
awk '
NF == 8 && $2 ~ /^[0-9]+$/ && $5 ~ /^[0-9]+$/ {
    a[$2] = 1
    b[$5] = 1
}
END {
    print "Number of contact residues chain A: ", length(a)
    print "Number of contact residues chain B: ", length(b)
    print "Number of interface residues:       ", length(a) + length(b)
}' "${out_file}.tmp" >> "${out_file}.tmp"

# Rename temporary file to final output
mv "${out_file}.tmp" "$out_file"
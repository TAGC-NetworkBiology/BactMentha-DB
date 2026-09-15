#!/bin/bash
# file is run from the _03_Script directory
# inputs
input_dir="../_01_References/AF3_predictions_hu/output_AF3_PDZ_PBM_NHERF2_2_clean"

# loop over .cif files in the AF3 predictions folder
for folder in "$input_dir"/af3_*/af3_PDZ_PBM_2; do
    for cif_file in "$folder"/*.cif; do
        name="$(basename "$cif_file" .cif)"
        out_file="${folder}/${name}.pdb"

        echo "Converting $cif_file to $out_file..."

        obabel "$cif_file" -O "$out_file"

    done
done

echo "All .cif files have been converted to .pdb files in their respective folders."

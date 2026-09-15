#!/bin/bash
# file is run from the _03_Script directory
# inputs
input_dir="../_01_References/AF3_predictions_hu/output_AF3_PDZ_PBM_NHERF2_2_clean"
output_dir="../_05_Output/AF3_contacts_hu/output_AF3_PDZ_PBM_NHERF2_2_clean"

# create output dir
mkdir -p "$output_dir"

# loop over .pdb files in the AF3 predictions folder
for folder in "$input_dir"/af3_*/af3_PDZ_PBM_2; do
    for pdb_file in "$folder"/*.pdb; do
    	# file name
        name="$(basename "$pdb_file" .pdb)"
        # run folder name
        run_name="$(basename "$(dirname "$folder")")"
        # output file name
        run_dir="${output_dir}/${run_name}"
        mkdir -p "$run_dir"
        out_file="${run_dir}/${name}_contact_table.txt"
        echo "Calculating contacts for $name..."
	# run contact binary and save output
	./residue_contacts_Interactome3D -t -C "$pdb_file" A B > "$out_file"

    done
done
echo "All .pdb files in $input_dir have been processed and the results were saved in $output_dir"

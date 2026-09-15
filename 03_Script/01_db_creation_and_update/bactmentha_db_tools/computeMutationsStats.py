# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Scirpt to compute the stats on mutations from interaction features and mimicint interfaces.
"""

from sys import path
import pandas as pd
from db_tasks import Db_tasks

class computeMutationsStats():

    def __init__( self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str ) -> None:
        """
        Initialize the createGraphs class.

        Parameters:
        - path_to_createdb (str): Path to the createdb module.
        - db_name (str): Database name.
        - db_host_name (str): Database host name.
        - db_user_name (str): Database user name.
        - db_pw (str): Database password.
        - db_port (str): Database port.
        """
        self.db = Db_tasks(db_name, db_host_name, db_user_name, db_pw, db_port)
        self.saving_path = "/BactMentha/03_Script/02_Website/static/stats/" # path from inside the container
        self.taxon_name_map = {'9606':'homo_sapiens',
                          '10090':'mus_musculus',
                          '10116':'rattus_norvegicus'}
        self.binding_regions = ['sufficient binding region', 'necessary binding region', 'binding-associated region']
        self.mutations = {'increasing_int_mutations' : ['mutation increasing interaction', 'mutation increasing interaction rate', 'mutation increasing interaction strength'],
                     'decreasing_int_mutations' : ['mutation decreasing interaction', 'mutation decreasing interaction rate', 'mutation decreasing interaction strength',
                                                   'mutation disrupting interaction', 'mutation disrupting interaction rate', 'mutation disrupting interaction strength'],
                     'causing_int_mutations' : ['mutation causing an interaction'],
                     'no_effect_mutations' : ['mutation with no effect'],
                     'unknown_effect_mutations' : ['mutation', 'variant']}
        self.mutations_list = ['mutation increasing interaction', 'mutation increasing interaction rate', 'mutation increasing interaction strength',
                               'mutation decreasing interaction', 'mutation decreasing interaction rate', 'mutation decreasing interaction strength',
                               'mutation disrupting interaction', 'mutation disrupting interaction rate', 'mutation disrupting interaction strength',
                               'mutation causing an interaction', 'mutation with no effect', 'mutation', 'variant']
        self.feature_table = self.getFeatureTable()
        self.interfaces_table = self.getInterfaceTable()
        
        self.human_data = self.getTaxonData('9606')
        self.mouse_data = self.getTaxonData('10090')
        self.rat_data = self.getTaxonData('10116')
        
        self.taxon_dict_map = {'9606' : self.human_data,
                               '10090' : self.mouse_data,
                               '10116': self.rat_data}
        
        # MAIN FUNCTION
        self.main()


    def main( self) -> None:
        """Execute the main logic to get graphs data and save them.
        
        Args:
        - cdb (__module__): The createdb module.

        Return: None
        """
        self.updateTaxonData( self.human_data )
        self.updateTaxonData( self.mouse_data )
        self.updateTaxonData( self.rat_data )
        self.saveStatsTables( )
        print("Successfully created the stats files !")


    def getTaxonData( self, taxon_id: str ) -> dict:
        """Get all the data (tables, statisitcs...) for the specified taxon.

        Args:
            taxon_id (str): '9606' for human, '10090' for mouse and '10116' for rat.

        Returns:
            dict: the stats as keys with the corresponding values.
        """
        temp_dict = {
            'id_for_mapping' : taxon_id,
            'Total interactions bmIds' : self.getTotalInteractions( taxon_id ),
            'feature_table' : self.getFeatureTableSubpart( taxon_id ),
        }
        temp_dict['bmId interactions with mutation data'] = set(self.getInteractionsWithMutationsData( temp_dict['feature_table'] )['mnt_interaction_id'].tolist())
        temp_dict['feature_table'] = self.filterBindingDataTable( temp_dict['feature_table'], temp_dict['bmId interactions with mutation data'])
        if taxon_id == '9606':
            temp_dict['interface_table'] = self.filterBindingDataTable( self.interfaces_table, temp_dict['bmId interactions with mutation data'])
        temp_dict['stats_on_A_mutations'] = self.initStatsTable()
        temp_dict['stats_on_B_mutations'] = self.initStatsTable()
        temp_dict['total_stats_mutations'] = self.initStatsTable()
        return temp_dict


    def getTotalInteractions( self, taxon_id: str ) -> list:
        """Get all the mnt_interaction_id corresponding to the specified taxon.

        Args:
            taxon (str): taxon_id to check

        Returns:
            list: all the mnt_interaction_id values in a list
        """
        statsQuery = "SELECT mnt_interaction_id FROM view_interaction_full_" + self.taxon_name_map[taxon_id] + ";"
        df = self.db.cursor_query_and_fetchall_in_df(statsQuery)
        return df


    def getFeatureTableSubpart( self, taxon_id: str ) -> pd.DataFrame:
        """Filter the feature table based on the taxon id of the host specie.

        Args:
            taxon (str): taxon id of the host

        Returns:
            pd.DataFrame: subtable filtered with host taxon identifier in the mnt_interaction_id columns.
        """
        return self.feature_table[self.feature_table['mnt_interaction_id'].str.startswith('bactmentha:BM-'+taxon_id+'-')]


    def getFeatureTable( self ) -> pd.DataFrame:
        """Get the complete interaction_fetaure table to later filter it depending on the taxon_id and the bmId
        to get only the rows refering to mnt_interaction_ids associated with mutations. 

        Returns:
            pd.DataFrame: the interaction_feature table.
        """
        sqlQuery = "SELECT * FROM interaction_feature;"
        df = self.db.cursor_query_and_fetchall_in_df(sqlQuery)
        return df


    def getInterfaceTable( self ) -> pd.DataFrame:
        """Get the complete interaction_fetaure table to later filter it depending on the taxon_id and the bmId
        to get only the rows refering to mnt_interaction_ids associated with mutations. 

        Returns:
            pd.DataFrame: the interaction_feature table.
        """
        sqlQuery = """SELECT * FROM mimicint_interface AS I
                    INNER JOIN mimicint_xref AS X
                    ON X.mimicint_interaction_id = I.mimicint_interaction_id;
        """
        df = self.db.cursor_query_and_fetchall_in_df(sqlQuery)
        return df


    def getInteractionsWithMutationsData( self, feature_table: pd.DataFrame ) -> pd.DataFrame:
        """Get a subpart of the taxon interaction_feature table corresponding to the rows with a bm_Id associated to at least one mutation
        in the features of interactor A or B.

        Args:
            feature_table (pd.DataFrame): table for binding regions taxon_id

        Returns:
            pd.DataFrame: filtered table with mutations data
        """
        first_filter = feature_table['feature_interactor_ida'].isin(self.mutations_list)
        second_filter = feature_table['feature_interactor_idb'].isin(self.mutations_list)
        temp_table = feature_table[first_filter | second_filter]
        return temp_table


    def filterBindingDataTable( self, table: pd.DataFrame, mutations_bmIds: list ) -> pd.DataFrame:
        """Filter the taxon feature/interfaces tables to keep only the rows where the mnt_interaction_id have been associated
        to a mutation feature at least once for either the pathogen or host interactor. Thus, the conserved rows may
        not contain directly interaction data, but binding regions in which the mutation can be located or not.

        Args:
            table (pd.DataFrame): complete table table (filtered only on the taxon id at first for features).
            mutations_bmIds (list): all the bmIds from the table that should be conserved.

        Returns:
            pd.DataFrame: filtered table on the taxon and on the bmIds associated to mutations.
        """
        return table[table['mnt_interaction_id'].isin(mutations_bmIds)]


    @staticmethod
    def initStatsTable( ) -> pd.DataFrame:
        """Creates an empty stats table to count the mutations that happen in or outside the binding regions or interfaces, and
        also count the mutations that have distinct effects like incresing the interaction (strength, rate...), decreasing it,
        causing the interaction, or sometimes having no effect or an unknown effect on the interaction.
        For each taxon, three tables will be created:
        - one for the mutations that appear in the pathogen protein sequence (interactor A)
        - one for the mutations that appear in the host protein sequence (interactor B)
        - one that sum the mutations of both A and B tables (both pathogen and host proteins mutations data)

        Returns:
            pd.DataFrame: empty stats table filled with 0.
        """
        row_names = ['increasing_int_mutations', 'decreasing_int_mutations', 'causing_int_mutations', 'no_effect_mutations', 'unknown_effect_mutations', 'total']
        column_names = ['in_BR_in_MI', 'out_BR_out_MI', 'in_BR_out_MI', 'out_BR_in_MI', 'in_BR_no_MI', 'out_BR_no_MI', 'no_BR_in_MI', 'no_BR_out_MI', 'no_BR_no_MI', 'total']
        return pd.DataFrame(0, index=row_names, columns=column_names)

        
    def updateTaxonData( self, taxon_dict: dict ) -> None:
        """Update the specified taxon dict to store the statistics on the mutations depending on there
        types/effect and their localisation for all the mnt_interaction_id that are associated with
        mutations. Retrieve all the features and mimicint interfaces associated to those interactions,
        stores the mutations and binding data independently then compare both. Finally, increments the
        stats values in the taxon dictionnary tables.

        Args:
            taxon_dict (dict): contains the stats tables and infos about the mutation-assocaited bmIds.
        """
        # Loop through the interaction identifiers associated with at least one mutation
        for bmid in taxon_dict['bmId interactions with mutation data']:
            feat_table = taxon_dict['feature_table'].loc[taxon_dict['feature_table']['mnt_interaction_id'] == bmid]
            
            if taxon_dict['id_for_mapping'] == '9606':
                int_table = taxon_dict['interface_table'].loc[taxon_dict['interface_table']['mnt_interaction_id'] == bmid]
                
            bmid_mutations_A = self.getMutationsData('ida', feat_table)
            bmid_mutations_B = self.getMutationsData('idb', feat_table)
            
            binding_positions_A = self.getBindingData('ida', feat_table, 'ft')
            binding_positions_B = self.getBindingData('idb', feat_table, 'ft')
            
            positions_to_check_A = [binding_positions_A]
            positions_to_check_B = [binding_positions_B]
            
            if taxon_dict['id_for_mapping'] == '9606':
                interfaces_positions_A = self.getBindingData('ida', int_table, 'mi')
                interfaces_positions_B = self.getBindingData('idb', int_table, 'mi')
                positions_to_check_A.append(interfaces_positions_A)
                positions_to_check_B.append(interfaces_positions_B) 
            
            if bmid_mutations_A['type'] != []:
                self.checkMutationsPositions(bmid_mutations_A, positions_to_check_A, 'A', taxon_dict)

            if bmid_mutations_B['type'] != []:
                self.checkMutationsPositions(bmid_mutations_B, positions_to_check_B, 'B', taxon_dict)


    def getMutationsData( self, id_ab: str, feat_table: pd.DataFrame) -> dict:
        """Get all the mutations informations form the feature subtable corresponding to a single 
        mnt_interaction_id, like its type (later used to categorize the effect of the mutation), its
        position, and an empty key called 'loc' for localization that will later be filled depending
        on whether the mutations falls inside our outside the binding regions/interfaces.

        Args:
            id_ab (str): 'ida' for pathogen protein, 'idb' for host protein.
            feat_table (pd.DataFrame): extract of the interaction_feature table for the current mnt_interaction_id.

        Returns:
            dict: the mutations informations for this bmId.
        """
        bmid_mutations = {'type' : [], 'start' : [], 'end' : [], 'loc' : []}
        for index, row in feat_table.iterrows():
            # Process interactor A (pathogen protein)
            if row['feature_interactor_'+id_ab] in self.mutations_list:
                bmid_mutations['type'].append(row['feature_interactor_'+id_ab])
                bmid_mutations['start'].append(row['feature_start_interactor_'+id_ab])
                bmid_mutations['end'].append(row['feature_end_interactor_'+id_ab])
        return bmid_mutations
    
    
    def getBindingData( self, id_ab: str, table: pd.DataFrame, ft_or_mi: str) -> list:
        """Get all the binding regions (not mutations) from the feature/interface subtable corresponding to a 
        single mnt_interaction_id (start and stop).

        Args:
            id_ab (str): 'ida' for pathogen protein, 'idb' for host protein.
            table (pd.DataFrame): extract of the feature or interface table for the current mnt_interaction_id.
            ft_or_mi (str): 'ft' if the current table is the feature subtable, 'mi' if it is the interface subtable.

        Returns:
            list: the binding regions positions in tuples (start, stop).
        """
        binding_positions = []
        for index, row in table.iterrows():
            if ft_or_mi == 'ft':
                row_data = [row['feature_interactor_'+id_ab], row['feature_start_interactor_'+id_ab], row['feature_end_interactor_'+id_ab]]
                if row_data[0] in self.binding_regions:
                    not_a_mutation = True
                else:
                    not_a_mutation = False
            else : #interface_type_ida	interface_start_interactor_ida	interface_end_interactor_ida
                row_data = [row['interface_type_'+id_ab], row['interface_start_interactor_'+id_ab], row['interface_end_interactor_'+id_ab]]
                not_a_mutation = True
            if all(data != '-' for data in row_data) and not_a_mutation:
                new_bind_data = (int(float(row_data[1].split('..')[0].split(',')[0])), int(float(row_data[2].split('..')[0].split(',')[0])))
                if new_bind_data not in binding_positions:
                    binding_positions.append(new_bind_data)
        return binding_positions


    def checkMutationsPositions( self, bmid_mutations: dict, positions_to_check: list, interactor: str, taxon_dict: dict ) -> None:
        """For each mutation associated to the current mnt_interaction_id, check if the mutations are falling
        inside or outside of the binding regions or interfaces if those informations are present.

        Args:
            bmid_mutations (dict): mutations types positions and empty location (in/out).
            positions_to_check (list): Contains one list for BR and eventually one for MI. Those lists contain
                tuples of start and end values for each distinct BR or MI.
            interactor (str): 'A' for the pathogen protein, 'B' for the host one.
            taxon_dict (dict): contains the stats tables to update.
        """
        nb_mutations = len(bmid_mutations['type'])
        for i in range(nb_mutations):
            m_type, m_start, m_end = bmid_mutations['type'][i], bmid_mutations['start'][i], bmid_mutations['end'][i]
            binding_positions = positions_to_check[0]
            # Checking mimicint interfaces
            isMI = 'no'
            if len(positions_to_check) > 1:
                interfaces_positions = positions_to_check[1]
                if len(interfaces_positions) != 0 :
                    isMI = 'out'
                    for binding_pair in interfaces_positions:
                        if ',' in m_start:
                            m_start = m_start.split(',')[0]
                        if ',' in m_end :
                            m_end = m_end.split(',')[1] 
                        if int(m_start) >= binding_pair[0] and int(m_end) <= binding_pair[1]:
                            isMI = 'in'
                            break
            # Checking binding regions
            isBR = 'no'
            if len(binding_positions) != 0:
                isBR = 'out'
                for binding_pair in binding_positions:
                    if ',' in m_start:
                        m_start = m_start.split(',')[0]
                    if ',' in m_end :
                        m_end = m_end.split(',')[1] 
                    if int(m_start) >= binding_pair[0] and int(m_end) <= binding_pair[1]:
                        isBR = 'in'
                        break
            # Getting mutation localization
            m_loc = isBR+'_BR_'+isMI+'_MI'
            self.updateStatsTables(taxon_dict['id_for_mapping'], interactor, m_type, m_loc)

 
    def updateStatsTables( self, id_for_mapping: str, interactor: str, m_type: str, m_loc: str) -> None:
        """Updates either 'A' or 'B' stat table, as well as the total table, for any mutation found in the feature table. Multiple updates
        can refer to a single mnt_interaction_id. The tables stats will be increased depending on the mutation type/effect, the interactor
        protein that contain the mutation (pathogen or host) and wether the mutation falls inside a binding region or a mimicint interface (if
        there are known binding data).

        Args:
            interactor (str): 'A' or 'B' depending on the table to update. 'Total' table will be updated anyway.
            m_type (str): type of the mutation that will be used to get the category of mutation effect
            m_loc (str): 'in' if inside a binding region or interface, 'out' if not found in any known binding region or interface, 
                        or 'no_bind_data' if no binding region or interface is known for the current interaction.
        """
        # check which table to update between A and B
        taxon_dict = self.taxon_dict_map[id_for_mapping]
        m_effect = None
        if interactor == 'A':
            table_to_update = taxon_dict['stats_on_A_mutations']
        else:
            table_to_update = taxon_dict['stats_on_B_mutations']
        # Find the category of mutation effects
        m_effect = "Error"
        for key, values in self.mutations.items():
            if m_type in values:
                m_effect = key
        # update table A or B
        table_to_update.at[m_effect, m_loc] += 1
        table_to_update.at[m_effect, 'total'] += 1
        table_to_update.at['total', m_loc] += 1
        table_to_update.at['total', 'total'] += 1
        # Also update the Total table
        table_to_update = taxon_dict['total_stats_mutations']
        table_to_update.at[m_effect, m_loc] += 1
        table_to_update.at[m_effect, 'total'] += 1
        table_to_update.at['total', m_loc] += 1
        table_to_update.at['total', 'total'] += 1


    def saveStatsTables( self ) -> None:
        """Saving the stats tables into txt files.
        
        Args:
        - saving_path (str): Path to the main folder that will contains the stats tables from inside the container.
        """
        tables_names = ['stats_on_A_mutations', 'stats_on_B_mutations', 'total_stats_mutations']
        for taxon_dict in [self.human_data, self.mouse_data, self.rat_data]:
            folder_path = self.saving_path + taxon_dict['id_for_mapping'] + '/'
            for table in tables_names:
                file_path = folder_path + table + '.txt'
                taxon_dict[table].to_csv(file_path, sep = '\t', )
                print(f'Saved {table} to {file_path}')

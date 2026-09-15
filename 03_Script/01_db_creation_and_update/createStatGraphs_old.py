# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Scirpt to create the website statistics page graphs and save them into the website static/img/graphs folder.
"""

from sys import path
import plotly.graph_objects as go
import pandas as pd

from bactmentha_db_tools.db_tasks import Db_tasks

class createGraphs():

    def __init__(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
        """
        Initialize the createGraphs class.

        Parameters:
        - db_name (str): Database name.
        - db_host_name (str): Database host name.
        - db_user_name (str): Database user name.
        - db_pw (str): Database password.
        - db_port (str): Database port.
        """
        self.bactmentha_db = Db_tasks(db_name, db_host_name, db_user_name, db_pw, db_port)
        # ATTRIBUTES
        self.graphsLabels = []
        self.graphsData = []
        self.graphsFigures = []
        self.graphsSavePath = []
        self.statsView = self.getSatsView()
        self.bactStatsGlobal = self.getBactStatsViews('global')
        self.bactStatsHuman = self.getBactStatsViews('homo_sapiens')
        self.bactStatsMouse = self.getBactStatsViews('mus_musculus')
        self.bactStatsRat = self.getBactStatsViews('rattus_norvegicus')
        # MAIN FUNCTION
        self.main()


    def main(self) -> None:
        """Execute the main logic to get graphs data and save them."""
        self.getGraphsLabels()
        self.getGraphsData()
        self.getGraphsSavePaths()
        self.getGraphsFigures()
        self.saveGraphsAtPaths()
        print("Successfully saved the Stats graphs !")
        

    def getSatsView(self) -> pd.DataFrame:
        """
        Retrieve graphical stats view from the database.

        Args: None

        Returns:
        - pd.DataFrame: DataFrame containing graphical stats.
        """
        query = "SELECT * FROM view_graphical_stats ORDER BY taxon;"
        df = self.bactmentha_db.cursor_query_and_fetchall_in_df(query)
        return df


    def getBactStatsViews(self, taxon_name:str) -> pd.DataFrame:
        """
        Retrieve bacterial stats view for a specific taxon from the database.

        Args:
        - taxon_name (str): The taxon name.

        Returns:
        - pd.DataFrame: DataFrame containing bacterial stats for the specified taxon.
        """
        query = self.create_bact_stats_query(taxon_name)
        df = self.bactmentha_db.cursor_query_and_fetchall_in_df(query)
        return df

    @staticmethod
    def create_bact_stats_query(taxon:str) -> str:
        """
        Create a bacterial stats query for a specific taxon.

        Args:
        - taxon_name (str): The taxon name.

        Returns:
        - str: SQL query for retrieving bacterial stats for the specified taxon.
        """
        if (taxon in ['global', 'homo_sapiens', 'mus_musculus', 'rattus_norvegicus']):
            return """
                    SELECT 
                        REGEXP_REPLACE(taxon_name, '\(strain.*\)', '') AS taxon_name,
                        SUM(number_of_interactions) AS total_interactions,
                        SUM(number_of_annotated_interactions) AS annotated_interactions
                    FROM 
                        view_bact_annotation_count_""" + taxon + """
                    GROUP BY 
                        REGEXP_REPLACE(taxon_name, '\(strain.*\)', '')
                    ORDER BY 
                        total_interactions DESC
                    LIMIT 10;"""
        else:
            print("Error in bacterial stats request (createStatsGraphs.py) : unavailable taxon. Available taxons are : 'global', 'homo_sapiens', 'mus_musculus', 'rattus_norvegicus'")


    def getGraphsLabels(self) -> None:
        """
        Retrieve labels for different graphs and populate the graphsLabels attribute.

        Args: None

        Return: None
        """
        # For graph 1 (Main chart)
        self.graphsLabels.append(["Human-bacteria protein-protein interactions", "Mouse-bacteria protein-protein interactions", 
                                  "Rat-bacteria protein-protein interactions"])
        # For graphs 2,3,4,5 (Proportion of annotations)
        self.graphsLabels.append(["Full annotated interactions", "Partially annotated interactions", "Unannotated interactions"])
        # For graphs 6,7,8,9 (Annotation types)
        self.graphsLabels.append(["MI", "MI+BR", "BR", "BR+PA", "PA", "MI+PA", "MI+BR+PA"])
        # For graphs 10,11,12,13 (Bacterial stats)
        self.graphsLabels.append("Total Interactions")


    def getGraphsData(self) -> None:
        """
        Retrieve data for different graphs and populate the graphsData attribute.

        Args: None

        Return: None
        """
        # For graph 1 (Main chart)
        self.graphsData.append(self.statsView['ni'][1:4].tolist())
        # For graphs 2,3,4,5 (Proportion of annotations)
        proportionData = self.statsView[['mfp', 'notfull', 'nomfp']] # [[]] to select columns in a specific order
        self.graphsData.append(proportionData.iloc[0].tolist()) # global
        self.graphsData.append(proportionData.iloc[1].tolist()) # human
        self.graphsData.append(proportionData.iloc[2].tolist()) # mouse
        self.graphsData.append(proportionData.iloc[3].tolist()) # rat
        # For graphs 6,7,8,9 (Annotation types)
        annotationTypes = self.statsView[['m', 'mf', 'f', 'fp', 'p', 'mp','mfp']]
        self.graphsData.append(annotationTypes.iloc[0].tolist()) # global
        self.graphsData.append(annotationTypes.iloc[1].tolist()) # human
        self.graphsData.append(annotationTypes.iloc[2].tolist()) # mouse
        self.graphsData.append(annotationTypes.iloc[3].tolist()) # rat


    def getGraphsSavePaths(self) -> None:
        """
        Generate and populate paths for saving graphs.

        Args: None
        
        Return: None
        """
        common_path = "/BactMentha/03_Script/02_Website/static/img/graphs/"
        self.graphsSavePath.append(common_path + "MainChart.html")
        self.graphsSavePath.append(common_path + "AnnotChart1_global.html")
        self.graphsSavePath.append(common_path + "AnnotChart1_human.html")
        self.graphsSavePath.append(common_path + "AnnotChart1_mouse.html")
        self.graphsSavePath.append(common_path + "AnnotChart1_rat.html")
        self.graphsSavePath.append(common_path + "AnnotChart2_global.html")
        self.graphsSavePath.append(common_path + "AnnotChart2_human.html")
        self.graphsSavePath.append(common_path + "AnnotChart2_mouse.html")
        self.graphsSavePath.append(common_path + "AnnotChart2_rat.html")
        self.graphsSavePath.append(common_path + "BactStatsChart_global.html")
        self.graphsSavePath.append(common_path + "BactStatsChart_human.html")
        self.graphsSavePath.append(common_path + "BactStatsChart_mouse.html")
        self.graphsSavePath.append(common_path + "BactStatsChart_rat.html")


    def getGraphsFigures(self) -> None:
        """
        Generate figures for different graphs and populate the graphsFigures attribute.

        Args: None
        
        Return: None
        """
        self.graphsFigures.append(self.createNumberOfInteractionsPerHostTaxonGraph(self.graphsData[0], self.graphsLabels[0])) # main chart
        # For graphs 2,3,4,5 (Proportion of annotations)
        self.graphsFigures.append(self.createAnnotationProportionGraph(self.graphsData[1], self.graphsLabels[1], 'Global'))
        self.graphsFigures.append(self.createAnnotationProportionGraph(self.graphsData[2], self.graphsLabels[1], 'Human'))
        self.graphsFigures.append(self.createAnnotationProportionGraph(self.graphsData[3], self.graphsLabels[1], 'Mouse'))
        self.graphsFigures.append(self.createAnnotationProportionGraph(self.graphsData[4], self.graphsLabels[1], 'Rat'))
        # For graphs 6,7,8,9 (Annotation types)
        self.graphsFigures.append(self.createAnnotationTypesGraph(self.graphsData[5], self.graphsLabels[2]))
        self.graphsFigures.append(self.createAnnotationTypesGraph(self.graphsData[6], self.graphsLabels[2]))
        self.graphsFigures.append(self.createAnnotationTypesGraph(self.graphsData[7], self.graphsLabels[2]))
        self.graphsFigures.append(self.createAnnotationTypesGraph(self.graphsData[8], self.graphsLabels[2]))
        # For graphs 10,11,12,13 (Bacterial stats)
        self.graphsFigures.append(self.createBacterialStatsGraph(self.bactStatsGlobal))
        self.graphsFigures.append(self.createBacterialStatsGraph(self.bactStatsHuman))
        self.graphsFigures.append(self.createBacterialStatsGraph(self.bactStatsMouse))
        self.graphsFigures.append(self.createBacterialStatsGraph(self.bactStatsRat))


    def createNumberOfInteractionsPerHostTaxonGraph(self, ChartData:list, ChartLabels:list) -> go.Figure:
        """
        Create a Pie chart showing the number of interactions per host taxon.

        Args:
        - ChartData (list): Data for the chart.
        - ChartLabels (list): Labels for the chart.

        Returns:
        - go.Figure: Plotly figure object.
        """
        displayed_labels = [label.split('-')[0] for label in ChartLabels]  # Extract first part of label
        fig = go.Figure()
        fig.add_trace(go.Pie(
            labels=ChartLabels,
            values=ChartData,
            hole=0.7,
            hoverinfo="label+percent+value",
            hovertemplate="%{label}: %{value} (%{percent})",
            text=displayed_labels,  # Use modified labels
            textinfo="none",  # Hide default text
            texttemplate="%{text}: %{value} (%{percent})",  # Format displayed text
            textposition="outside",  # Moves text labels outside the chart
            marker=dict(colors=["#429E9D", "#3EB489", "#93E9BE"],
                        line=dict(color='#FFFFFF', width=2)),
        ))
        fig.update_layout(
            title="",
            showlegend=True,
            legend=dict(
                x=1,  # Moves legend to the right
                y=0.5,  # Centers legend vertically
                xanchor="left",  # Ensures legend stays to the right of the chart
                yanchor="middle",  # Centers legend in the middle of the chart
            ),
            margin=dict(t=20, b=20, l=20, r=20),  # Set reasonable default margins
            uniformtext_minsize=12,  # Ensures text remains readable
            uniformtext_mode='hide'  # Prevents text overlap
        )
        return fig


    def createAnnotationProportionGraph(self, ChartData:list, ChartLabels:list, Taxon_name:str) -> go.Figure:
        """
        Create a stacked bar chart showing the proportion of annotations for a specific taxon.

        Args:
        - ChartData (list): Data for the chart.
        - ChartLabels (list): Labels for the chart.
        - Taxon_name (str): Name of the taxon.

        Returns:
        - go.Figure: Plotly figure object.
        """
        fig = go.Figure()
        fig.add_trace(go.Bar(
            x=ChartLabels,
            y=ChartData,
            marker=dict(color=["#feba4f", "#429e9d", "#A5A5A8"]),
            text=ChartData,
            textposition='outside',
            textfont=dict(size=12)  # Reduce text size if needed
        ))
        fig.update_layout(
            yaxis=dict(range=[0, max(ChartData) * 1.2], automargin=True),  # Ensure enough space above bars
            barmode='stack',
            title="",
            showlegend=False,
            margin=dict(t=0, b=0)  # Increase top margin
        )
        return fig


    def createAnnotationTypesGraph(self, ChartData:list, ChartLabels:list) -> go.Figure:
        """
        Create a Pie chart showing the distribution of annotation types.

        Args:
        - ChartData (list): Data for the chart.
        - ChartLabels (list): Labels for the chart.

        Returns:
        - go.Figure: Plotly figure object.
        """
        fig = go.Figure()
        fig.add_trace(go.Pie(
            labels=ChartLabels,
            values=ChartData,
            hole=0.7,
            hoverinfo="label+percent+value",
            hovertemplate="%{label}: %{value} (%{percent})",
            text=ChartLabels,
            textinfo="none",  # Hide default text
            texttemplate="%{text}: %{value} (%{percent})",  # Format displayed text
            textposition="outside",  # Moves text labels outside the chart
            marker=dict(colors=["#52a097", "#71a58a", "#90aa7d", "#b0ae6f", "#cfb362", "#eeb855", "#F6A951"],
                        line=dict(color='#FFFFFF', width=2)),
            rotation=90,  # Rotates the pie chart to start from the bottom
        ))
        fig.update_layout(
            title="",
            showlegend=False,
            autosize=True,  # Automatically adjusts to content
            height=300,
            margin=dict(t=0, b=0),
            uniformtext_minsize=12,  # Ensures text remains readable
            uniformtext_mode='hide'  # Prevents text overlap
        )
        return fig


    def format_number(self, num):
        if num >= 1000:
            return f"{num // 1000}k"  # Divide by 1000 and append 'k'
        return str(num)  # Otherwise, return the number as a string


    def createBacterialStatsGraph(self, bactStats:pd.DataFrame) -> go.Figure:
        """
        Create a bar chart showing bacterial statistics.

        Args:
        - bactStats (pd.DataFrame): DataFrame containing bacterial statistics.

        Returns:
        - go.Figure: Plotly figure object.
        """
        fig = go.Figure()

        # Create a single bar trace with stacked data
        fig.add_trace(go.Bar(
            x=bactStats['taxon_name'],
            y=bactStats['total_interactions'],
            name='',
            hovertemplate='Total Interactions: %{y}',
            # text=[],
            # hoverinfo='y',  # Display y-value only when hovered
            marker_color='#429E9D',
            text=[f"{self.format_number(t)}/{self.format_number(a)}" for t, a in zip(bactStats['total_interactions'], 
                                                                                     bactStats['annotated_interactions'])],
            textposition='outside',
            textfont=dict(size=10),  # Reduce text size if needed
            opacity=1
        ))
        fig.add_trace(go.Bar(
            x=bactStats['taxon_name'],
            y=bactStats['annotated_interactions'],
            name='',
            hovertemplate='Annotated Interactions: %{y}',
            # text=[],
            # hoverinfo='y',  # Display y-value only when hovered
            marker_color='#3EB489',
            opacity=1
        ))
        # Update layout options
        fig.update_layout(
            title="",
            xaxis_title='Bacterial taxons',
            yaxis_title=None,
            yaxis=dict(range=[0, float(bactStats['total_interactions'].max()) * 1.2]),  # Increase max range by 20%
            uniformtext_minsize=10,  # Ensures text remains readable
            barmode='overlay',
            margin=dict(t=0, b=0, l=0, r=0),
            showlegend=False
        )
        return fig


    def saveGraphsAtPaths(self) -> None:
        """
        Save generated graphs at specified paths.

        Return: None
        """
        for i in range(len(self.graphsFigures)):
            self.graphsFigures[i].write_html(self.graphsSavePath[i])


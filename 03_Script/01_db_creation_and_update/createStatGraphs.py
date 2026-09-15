# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Scirpt to create the website statistics page graphs and save them into the website static/img/graphs folder.
"""

import plotly.graph_objects as go
import pandas as pd
from os import path
import matplotlib.pyplot as plt
import numpy as np

from bactmentha_db_tools.db_tasks import Db_tasks

from constants import SCRIPT

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
        self.common_path = path.join(SCRIPT, "02_Website", "static", "img", "graphs")
        self.name_to_table_map = {
            "Global": "interaction_full",
            "Human": "view_interaction_full_homo_sapiens", 
            "Mouse": "view_interaction_full_mus_musculus",  
            "Rat": "view_interaction_full_rattus_norvegicus", 
        }
        # Global interactions counts per host 
        self.plot_global_interactions_counts_per_host()
        # Fully, partially or unannotated interactions global + per host
        self.plot_full_or_partially_annotated_interactions("Global")
        self.plot_full_or_partially_annotated_interactions("Human")
        self.plot_full_or_partially_annotated_interactions("Mouse")
        self.plot_full_or_partially_annotated_interactions("Rat")
        # Proportions of annotations types among annotated interactions
        self.plot_upset_annotations_combinations_html()
        # # Proportions of annotations types among annotated interactions per host (those that were partially OR full) with N=Number !
        self.plot_proportions_annotations_per_host_in_annotated_interactions("Human")
        self.plot_proportions_annotations_per_host_in_annotated_interactions("Mouse")
        self.plot_proportions_annotations_per_host_in_annotated_interactions("Rat")
        # # NUmber of interactions involving annotated bacterial proteins per main bacterial taxa against each host
        self.plot_interactions_with_PA_among_total_for_bact_taxa("Global")
        self.plot_interactions_with_PA_among_total_for_bact_taxa("Human")
        self.plot_interactions_with_PA_among_total_for_bact_taxa("Mouse")
        self.plot_interactions_with_PA_among_total_for_bact_taxa("Rat")


    def plot_global_interactions_counts_per_host(self) -> None:
        """Number of interactions for each host taxon in BactMentha.
        Plot an html interactive image with PLotly, with hover text data, and numbers for each part
        value of the donut plot representing all the interactions in BactMentha.
        """
        chartLabels = []
        chartData = []
        ColorsLabels = []
        # queries
        for label in ["Human", "Mouse", "Rat"]:
            chartLabels.append(label)
            table = self.name_to_table_map[label]
            query = f"SELECT COUNT(DISTINCT IF.mnt_interaction_id) AS value FROM {table} AS IF;"
            chartData.append(int(self.bactmentha_db.cursor_query_and_get_first_result(query)))
            ColorsLabels.append(f"{label}-bacteria protein-protein interactions")
        # perform and save plot
        fig = go.Figure()
        fig.add_trace(go.Pie(
            labels=ColorsLabels,
            values=chartData,
            hole=0.7,
            hoverinfo="label+percent+value",
            hovertemplate="%{label}: %{value} (%{percent})",
            text=chartLabels,
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
        fig.write_html(path.join(self.common_path, "MainChart.html"))


    def plot_full_or_partially_annotated_interactions(self, group:str) -> None:
        """Get the barplot of fully, partially and unannanotated interactions among global and for
        each host taxon dataset.

        Args:   
            group (str): "Global", "Human", "Mouse", "Rat".
        """
        chartLabels = ["Full annotated interactions",
                       "Partially annotated interactions",
                       "Unannotated interactions"]
        chartData = []
        # queries
        table = self.name_to_table_map[group]
        total_ints_query = f"""SELECT COUNT(DISTINCT IF.mnt_interaction_id) AS value
                            FROM {table} AS IF"""
        full_annot_query = f"""SELECT COUNT(DISTINCT IF.mnt_interaction_id) AS value
                            FROM {table} AS IF WHERE IF.annotations = '11111'"""
        no_annot_query = f"""SELECT COUNT(DISTINCT IF.mnt_interaction_id) AS value
                            FROM {table} AS IF WHERE IF.annotations = '00000'"""
        total_ints = int(self.bactmentha_db.cursor_query_and_get_first_result(total_ints_query))
        fully_annot = int(self.bactmentha_db.cursor_query_and_get_first_result(full_annot_query))
        no_annot = int(self.bactmentha_db.cursor_query_and_get_first_result(no_annot_query))
        partial_annot = total_ints - fully_annot - no_annot
        # Counts
        chartData = [fully_annot, partial_annot, no_annot]
        # perform and save plot
        fig = go.Figure()
        fig.add_trace(go.Bar(
            x=chartLabels,
            y=chartData,
            marker=dict(color=["#feba4f", "#429e9d", "#A5A5A8"]),
            text=chartData,
            textposition='outside',
            textfont=dict(size=12)  # Reduce text size if needed
        ))
        fig.update_layout(
            yaxis=dict(range=[0, max(chartData) * 1.2], automargin=True),  # Ensure enough space above bars
            barmode='stack',
            title="",
            showlegend=False,
            margin=dict(t=0, b=0)  # Increase top margin
        )
        fig.write_html(path.join(self.common_path, f"AnnotChart1_{group}.html"))


    def plot_upset_annotations_combinations_html(self) -> None:
        """Creates an interactive upset plot to display all combinations of annotations in PA, CC, BR, MI and AF."""
        connection = self.bactmentha_db.create_server_connection()
        get_intfull_query = "SELECT * from interaction_full;"
        intfull = pd.read_sql_query(get_intfull_query, connection)
        connection.close()
        rows = []
        colors = {"Default": "#cbcbcb", "PA": "#f6a951", "CC": "#dfb763",
                "BR": "#3eb489", "MI": "#419d9d", "AF": "#5985c1"}
        for _, row in intfull.iterrows():
            PA = True if row["annotations"][0] == "1" else False
            CC = True if row["annotations"][1] == "1" else False
            BR = True if row["annotations"][2] == "1" else False
            MI = True if row["annotations"][3] == "1" else False
            AF = True if row["annotations"][4] == "1" else False
            rows.append({
                "mnt_interaction_id": row["mnt_interaction_id"],
                "PA": PA, "CC": CC, "BR": BR, "MI": MI, "AF": AF,
            })
        df = pd.DataFrame(rows)
        categories = ["PA", "CC", "BR", "MI", "AF"]
        # Regroupement par combinaison et comptage
        combo_counts = (
            df.groupby(categories)
            .size()
            .reset_index(name="count")
            .sort_values("count", ascending=False)
            .reset_index(drop=True)
        )
        n_combos = len(combo_counts)
        n_cats = len(categories)
        total = len(df)
        # ------------------------------------------------------------
        # Figure
        # ------------------------------------------------------------
        fig = go.Figure()
        # ------------------------------------------------------------
        # BARPLOT - partie supérieure
        # ------------------------------------------------------------
        bar_customdata = []
        for _, combo_row in combo_counts.iterrows():
            active_categories = [cat for cat in categories if bool(combo_row[cat])]
            combination = " + ".join(active_categories) if active_categories else "None"
            count = int(combo_row["count"])
            percentage = count / total * 100
            bar_customdata.append([combination, count, percentage])
        fig.add_trace(go.Bar(
            x=list(range(n_combos)),
            y=combo_counts["count"],
            marker_color="#328076",
            width=0.7,
            customdata=bar_customdata,
            hovertemplate=(
                "<b>%{customdata[0]}</b><br>"
                "%{customdata[1]:,} interactions "
                "(%{customdata[2]:.1f}%)"
                "<extra></extra>"
            ),
            showlegend=False,
            xaxis="x",
            yaxis="y"
        ))
        for x_pos, count in enumerate(combo_counts["count"]):
            fig.add_annotation(
                x=x_pos,
                y=int(count),
                text=f"{int(count):,}",
                showarrow=False,
                yshift=5,
                font=dict(size=9),
                xref="x",
                yref="y"
            )
        # ------------------------------------------------------------
        # MATRICE - partie inférieure
        # ------------------------------------------------------------
        # Lignes horizontales de fond
        for y in range(n_cats):
            fig.add_trace(go.Scatter(
                x=[-0.5, n_combos - 0.5],
                y=[y, y],
                mode="lines",
                line=dict(color="#eeeeee", width=8),
                hoverinfo="skip",
                showlegend=False,
                xaxis="x",
                yaxis="y2"
            ))
        # Lignes verticales reliant les annotations présentes
        # Elles sont créées AVANT les points afin de rester derrière ceux-ci
        for x_pos, (_, combo_row) in enumerate(combo_counts.iterrows()):
            true_y = [y for y, cat in enumerate(categories) if bool(combo_row[cat])]

            if len(true_y) > 1:
                fig.add_trace(go.Scatter(
                    x=[x_pos, x_pos],
                    y=[min(true_y), max(true_y)],
                    mode="lines",
                    line=dict(color="#328076", width=2),
                    hoverinfo="skip",
                    showlegend=False,
                    xaxis="x",
                    yaxis="y2"
                ))

        # Points de la matrice
        for x_pos, (_, combo_row) in enumerate(combo_counts.iterrows()):
            for y, cat in enumerate(categories):
                is_true = bool(combo_row[cat])

                fig.add_trace(go.Scatter(
                    x=[x_pos],
                    y=[y],
                    mode="markers",
                    marker=dict(
                        size=12,
                        color=colors[cat] if is_true else colors["Default"],
                        line=dict(color="white", width=0.5)
                    ),
                    hovertemplate=(
                        f"<b>{cat}</b><br>"
                        f"{'Present' if is_true else 'Absent'}"
                        "<extra></extra>"
                    ),
                    showlegend=False,
                    xaxis="x",
                    yaxis="y2"
                ))
        # ------------------------------------------------------------
        # LAYOUT
        # ------------------------------------------------------------
        fig.update_layout(
            autosize=True,
            # width=max(800, n_combos * 30),
            # height=800,
            plot_bgcolor="white",
            paper_bgcolor="white",
            showlegend=False,
            margin=dict(l=10, r=10, t=10, b=10),
            # Axe X partagé par les deux parties
            xaxis=dict(
                domain=[0, 1],
                range=[-0.5, n_combos - 0.5],
                showgrid=False,
                zeroline=False,
                showticklabels=False,
                ticks="",
            ),
            # Y du barplot
            yaxis=dict(
                domain=[0.38, 1],
                showgrid=False,
                zeroline=False,
                showline=False,
                showticklabels=True,
                title="",
            ),
            # Y de la matrice
            yaxis2=dict(
                domain=[0.01, 0.36],
                range=[n_cats - 0.5, -0.5],
                tickmode="array",
                tickvals=list(range(n_cats)),
                ticktext=categories,
                showgrid=False,
                zeroline=False,
                showline=False,
                showticklabels=True,
                ticks="",
                title="",
            ),
        )
        # ------------------------------------------------------------
        # Sauvegarde
        # ------------------------------------------------------------
        fig.write_html(
            path.join(self.common_path, "AnnotChart2_Global.html"),
            include_plotlyjs=True
        )


    def plot_upset_annotations_combinations_png(self) -> None:
        """Creates and upset plot to display all the combintions of annotations in PA, CC, BR, MI and AF.
        """
        connection = self.bactmentha_db.create_server_connection()
        get_intfull_query = "SELECT * from interaction_full;"
        intfull = pd.read_sql_query(get_intfull_query, connection)
        connection.close()
        rows = []
        colors = {"Default": "#cbcbcb", "PA": "#f6a951", "CC": "#dfb763",
                  "BR": "#3eb489", "MI": "#419d9d", "AF": "#5985c1"}
        for _, row in intfull.iterrows(): 
            PA = True if row["annotations"][0] == "1" else False # example: 10010 -> True
            CC = True if row["annotations"][1] == "1" else False # example: 10010 -> False
            BR = True if row["annotations"][2] == "1" else False # example: 10010 -> False
            MI = True if row["annotations"][3] == "1" else False # example: 10010 -> True
            AF = True if row["annotations"][4] == "1" else False # example: 10010 -> False
            rows.append({
                "mnt_interaction_id": row["mnt_interaction_id"],
                "PA": PA, "CC": CC, "BR": BR, "MI": MI, "AF": AF,
            })
        df = pd.DataFrame(rows)
        categories = ["PA", "CC", "BR", "MI", "AF"]
        # Regroupement par combinaison et comptage
        combo_counts = (
            df.groupby(categories)
            .size()
            .reset_index(name="count")
            .sort_values("count", ascending=False)
            .reset_index(drop=True)
        )
        n_combos = len(combo_counts)
        n_cats = len(categories)
        fig = plt.figure(figsize=(max(10, n_combos * 0.4), 8))
        gs = fig.add_gridspec(2, 1, height_ratios=[3, 1.5], hspace=0.05)
        ax_bar = fig.add_subplot(gs[0])
        ax_matrix = fig.add_subplot(gs[1], sharex=ax_bar)
        # --- Barplot (haut) ---
        x_positions = range(n_combos)
        bars = ax_bar.bar(x_positions, combo_counts["count"], color="#328076", width=0.8)
        for bar, count in zip(bars, combo_counts["count"]):
            ax_bar.text(
                bar.get_x() + bar.get_width() / 2,
                bar.get_height(),
                str(count),
                ha="center", va="bottom", fontsize=10,
            )
        ax_bar.set_ylabel("")
        ax_bar.spines["top"].set_visible(False)
        ax_bar.spines["right"].set_visible(False)
        ax_bar.spines["bottom"].set_visible(False)
        ax_bar.spines["left"].set_visible(False)
        ax_bar.tick_params(axis="x", bottom=False, labelbottom=False)
        # --- Matrice (bas) ---
        y_positions = range(n_cats)
        # Lignes de fond légères pour la lisibilité
        for y in y_positions:
            ax_matrix.axhline(y, color="#eeeeee", zorder=0, linewidth=8)
        for x, (_, combo_row) in enumerate(combo_counts.iterrows()):
            true_y = []
            for y, cat in enumerate(categories):
                is_true = bool(combo_row[cat])
                dot_color = colors[cat] if is_true else colors["Default"]
                ax_matrix.scatter(
                    x, y, s=180, color=dot_color, zorder=2,
                    edgecolors="white", linewidths=0.5,
                )
                if is_true:
                    true_y.append(y)
            # Ligne verticale reliant les points actifs (style upset plot classique)
            if len(true_y) > 1:
                ax_matrix.plot(
                    [x, x], [min(true_y), max(true_y)],
                    color="#328076", zorder=1, linewidth=1.5,
                )
        ax_matrix.set_yticks(list(y_positions))
        ax_matrix.set_yticklabels(categories)
        ax_matrix.set_ylim(-0.5, n_cats - 0.5)
        ax_matrix.invert_yaxis()
        ax_matrix.set_xlim(-0.5, n_combos - 0.5)
        ax_matrix.set_xticks([])
        for spine in ["top", "right", "left", "bottom"]:
            ax_matrix.spines[spine].set_visible(False)
        fig.suptitle("", fontsize=14)
        plt.tight_layout()
        plt.savefig(path.join(self.common_path, "AnnotChart2_Global.png"), dpi=300, bbox_inches="tight")


    def plot_proportions_annotations_per_host_in_annotated_interactions(self, group:str) -> None:
        """Plot the proportion of interactions covered by the given annotations for the
        corresponding taxon goupe
        """
        connection = self.bactmentha_db.create_server_connection()
        table = self.name_to_table_map[group]
        get_intfull_query = f"SELECT * from {table};"
        intfull = pd.read_sql_query(get_intfull_query, connection)
        connection.close()
        rows = []
        for _, row in intfull.iterrows(): 
            PA = True if row["annotations"][0] == "1" else False # example: 10010 -> True
            CC = True if row["annotations"][1] == "1" else False # example: 10010 -> False
            BR = True if row["annotations"][2] == "1" else False # example: 10010 -> False
            MI = True if row["annotations"][3] == "1" else False # example: 10010 -> True
            AF = True if row["annotations"][4] == "1" else False # example: 10010 -> False
            rows.append({
                "mnt_interaction_id": row["mnt_interaction_id"],
                "PA": PA, "CC": CC, "BR": BR, "MI": MI, "AF": AF,
            })
        df = pd.DataFrame(rows)
        counts = {
            "Total": len(df),
            "PA": len(df[df["PA"] == True]),
            "CC": len(df[df["CC"] == True]),
            "BR": len(df[df["BR"] == True]),
            "MI": len(df[df["MI"] == True]),
            "AF": len(df[df["AF"] == True]),
        }
        colors = {"Default": "#eeeeee", "PA": "#f6a951", "CC": "#dfb763",
                  "BR": "#3eb489", "MI": "#419d9d", "AF": "#5985c1"}
        for cat in ["PA", "CC", "BR", "MI", "AF"]:
            counts[f"{cat}_percent"] = counts[cat] / counts["Total"]
        # ------------------------------------------------------------
        # Interactive Plotly plot
        # ------------------------------------------------------------
        categories = ["PA", "CC", "BR", "MI", "AF"]
        fig = go.Figure()
        for cat in categories:
            percentage = counts[f"{cat}_percent"]
            count = counts[cat]
            fig.add_trace(go.Bar(
                x=[cat],
                y=[percentage],
                base=0,
                width=0.65,
                marker_color=colors[cat],
                customdata=[[count, percentage * 100]],
                hovertemplate=(
                    f"<b>{cat}</b>: "
                    "%{customdata[0]:,} interactions "
                    "(%{customdata[1]:.1f}%)"
                    "<extra></extra>"
                ),
                showlegend=False
            ))
        # Background bars = 100%
        for cat in categories:
            fig.add_trace(go.Bar(
                x=[cat],
                y=[1],
                base=0,
                marker_color=colors["Default"],
                width=0.65,
                hoverinfo="skip",
                showlegend=False
            ))
        # Colored bars must appear in front of background bars
        # Therefore reorder traces: background first, colored second
        fig.data = tuple(
            list(fig.data[len(categories):]) +
            list(fig.data[:len(categories)])
        )
        # Layout
        fig.update_layout(
            width=700,
            height=500,
            plot_bgcolor="white",
            paper_bgcolor="white",
            barmode="overlay",
            margin=dict(l=70, r=30, t=30, b=70),
            showlegend=False,
            xaxis=dict(
                showgrid=False,
                zeroline=False,
                showline=True,
                linecolor="black",
                ticks="",
            ),
            yaxis=dict(
                range=[0, 1.05],
                tickmode="array",
                tickvals=np.arange(0, 1.01, 0.2),
                ticktext=[f"{int(v * 100)}%" for v in np.arange(0, 1.01, 0.2)],
                showgrid=False,
                zeroline=False,
                showline=True,
                linecolor="black",
            ),
        )
        # Percentage labels above colored bars
        for cat in categories:
            percentage = counts[f"{cat}_percent"]
            fig.add_annotation(
                x=cat,
                y=percentage,
                text=f"{percentage * 100:.1f}%",
                showarrow=False,
                yshift=8,
                font=dict(size=10),
            )
        # Save
        output_path = path.join(self.common_path, f"AnnotChart2_{group}")
        fig.write_html(output_path + ".html", include_plotlyjs=True)

    @staticmethod
    def format_number(num):
        if num >= 1000:
            return f"{num // 1000}k"  # Divide by 1000 and append 'k'
        return str(num)  # Otherwise, return the number as a string


    def plot_interactions_with_PA_among_total_for_bact_taxa(self, group:str) -> None:
        """
        Create a bar chart showing bacterial statistics.
        """
        mapper = {
            "Global": "global", 
            "Human": "homo_sapiens", 
            "Mouse": "mus_musculus", 
            "Rat": "rattus_norvegicus",
        }
        table = self.name_to_table_map[group]
        connection = self.bactmentha_db.create_server_connection()
        query = f"""SELECT 
                REGEXP_REPLACE(UT.taxon_name, ' [(]strain.*', '') AS taxon_name,
                COUNT(*) AS total_interactions,
                SUM(CASE WHEN SUBSTRING(IF.annotations, 1, 1) = '1' THEN 1 ELSE 0 END) AS annotated_interactions
            FROM 
                {table} AS IF
            INNER JOIN
                uniprot_taxonomy AS UT ON IF.taxon_interactor_ida = UT.taxon_id
            GROUP BY 
                REGEXP_REPLACE(UT.taxon_name, ' [(]strain.*', '')
            ORDER BY 
                total_interactions DESC
            LIMIT 10;"""
        df = pd.read_sql_query(query, connection)
        connection.close()

        fig = go.Figure()
        # Create a single bar trace with stacked data
        fig.add_trace(go.Bar(
            x=df['taxon_name'],
            y=df['total_interactions'],
            name='',
            hovertemplate='Total Interactions: %{y}',
            # text=[],
            # hoverinfo='y',  # Display y-value only when hovered
            marker_color='#429E9D',
            text=[f"{self.format_number(a)}/{self.format_number(t)}" for a, t in zip(df['annotated_interactions'], 
                                                                                     df['total_interactions'])],
            textposition='outside',
            textfont=dict(size=10),  # Reduce text size if needed
            opacity=1
        ))
        fig.add_trace(go.Bar(
            x=df['taxon_name'],
            y=df['annotated_interactions'],
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
            yaxis=dict(range=[0, float(df['total_interactions'].max()) * 1.2]),  # Increase max range by 20%
            uniformtext_minsize=10,  # Ensures text remains readable
            barmode='overlay',
            margin=dict(t=0, b=0, l=0, r=0),
            showlegend=False
        )
        fig.write_html(path.join(self.common_path, f"BactStatsChart_{group}.html"))


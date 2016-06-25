<?php
namespace TS\ApiBundle\Algorithm\SwissLadder\SharpShuttle;

	/// <summary>
	/// Een geoptimaliseerde graaf implementatie die maximaal 32 knopen bevat
	/// </summary>
	class Graph32 extends Graph
	{
		/// <summary>
		/// De knopen
		/// </summary>
		public $data;

		/// <summary>
		/// Maakt een nieuwe Graph32met size knopen (maximaal 32)
		/// </summary>
		/// <param name="size">Het aantal knopen in de Graph32 (maximaal 32)</param>
		public function Graph32($size)
		{
			if($size > 32)
			{
				throw new Exception("Max graph size exeeded.");
			}

			$this->data = array();
//			for($i = 0; $i < 64; $i++)
//			{
//				$this->data[$i] = 0;
//			}
		}

		/// <summary>
		/// Voegt een ongerichte kant toe tussen node0 en node1
		/// </summary>
		public function AddUndirectedEdge($node0, $node1)
		{
			$this->data[$node0] |= 1 << $node1;
			$this->data[$node1] |= 1 << $node0;
		}

		/// <summary>
		/// Haalt de kant(en) tussen node0 en node1 weg
		/// </summary>
		public function RemoveUndirectedEdge($node0, $node1)
		{
			$this->data[$node0] &= ~(1 << $node1);
			$this->data[$node1] &= ~(1 << $node0);
		}

		public function getEdge($x, $y)
		{
			return ($this->data[$x] & (1 << $y)) != 0;
		}

		public function setEdge($x, $y, $value)	
		{
			if ($value)
				$this->data[$x] |= 1 << $y;
			else
				$this->data[$x] &= ~(1 << $y);
		}
	

		/// <summary>
		/// Test of de Graph samenhangende subgraphs heeft met een oneven aantal knopen
		/// </summary>
		/// <returns>true als de Graph minstens 1 subgraph met oneven aantal knopen heeft</returns>
		public function HasUnevenSubgraph()
		{
			//in het begin mag je alle nodes nog bezoeken
			//dus alle nodes die echt bestaan krijgen een 1
			//de rest zijn "vullers" om de 32 bits vol te maken
			$undiscoveredNodes = PHP_INT_MAX >> (64 - sizeof($this->data));

			while ($undiscoveredNodes != 0)
			{
				//maak de laagste 1bit 0
				$newUndiscovered = $undiscoveredNodes & ($undiscoveredNodes - 1);
				//in node is maar 1 bit 1, namelijk de bit
				//die 0 geworden is in de regel hierboven
				$node = $newUndiscovered ^ $undiscoveredNodes;
				$undiscoveredNodes = $newUndiscovered;

				// CHECK Kan hier mogelijk fout gaan
				if (($this->countSubgraph($undiscoveredNodes, $node) & 1) == 1)
					return true;
			}

			return false;
		}

		/// <summary>
		/// Telt het aantal knopen in de deelgraaf die de node 'node' bevat
		/// </summary>
		/// <param name="undiscovered">De nodes die je nog mag bezoeken</param>
		/// <param name="node">De node van waaruit je begint te zoeken</param>
		/// <returns>Aantal knopen in de deelgraaf waar 'node' in zit</returns>
		function countSubgraph(&$undiscovered, $node)
		{
			//local copy om accesses via een ref te voorkomen
			$undis = $undiscovered;
			//dit is zegmaar "de stack", nodes waar je nog naar moet kijken
			//hebben een 1 in mayDiscover
			$mayDiscover = $node;
			$count = 0;

			//special case - alle nodes zijn al bezocht dus deze
			//subgraph is maar 1 groot
			if ($undis == 0)
				return 1;

			while ($mayDiscover != 0)
			{
				//zelfde truc als in HasUnevenSubgraph
				//reset de laagste 1bit en pak daarna die bit
				$newMayDiscover = $mayDiscover & ($mayDiscover - 1);
				$discovered = $newMayDiscover ^ $mayDiscover;
				$mayDiscover = $newMayDiscover;
				$count++;

				//bereken de index die correspondeert met de mask
				// CHECK Kan mogelijk fout gaan hier
				//$index = BinaryLogarithm.log2($discovered);
				$index = log($discovered, 2);

				//voeg undiscovered nodes toe waarnaar node[index] een edge heeft
				$mayDiscover |= ($this->data[$index] & $undis);
				//kleur alle nodes die gezien zijn
				$undis &= ~$mayDiscover;
			}

			$undiscovered = $undis;
			return $count;
		}

		/// <summary>
		/// Maakt alle mogenlijk edges aan
		/// </summary>
		public function Fill()
		{
			for ($i = 0; $i < 64; $i++)
			{
				$this->data[$i] = PHP_INT_MAX;
			}
		}

		/// <summary>
		/// Het aantal knopen in de Graph
		/// </summary>
		public function GetCount()
		{
			return sizeof($this->data);
		}

		// public static void Test()
		// {
		// 	Random r = new Random();
		// 	const int graphSize = 32;
		// 	Graph32 g32 = new Graph32(graphSize);
		// 	Graph g = new BitGraph(graphSize);

		// 	for (int j = 0; j < graphSize; j++)
		// 	{
		// 		int x = r.Next(0, graphSize);
		// 		int y = r.Next(0, graphSize);
		// 		g.AddUndirectedEdge(x, y);
		// 		g32.AddUndirectedEdge(x, y);
		// 	}

		// 	for (int i = 0; i < 5000; i++)
		// 	{
		// 		for (int j = 0; j < graphSize / 4; j++)
		// 		{
		// 			int x = r.Next(0, graphSize);
		// 			int y = r.Next(0, graphSize);

		// 			if (r.NextDouble() > 0.90)
		// 			{
		// 				g.AddUndirectedEdge(x, y);
		// 				g32.AddUndirectedEdge(x, y);
		// 			}
		// 			else
		// 			{
		// 				g.RemoveUndirectedEdge(x, y);
		// 				g32.RemoveUndirectedEdge(x, y);
		// 			}
		// 		}

		// 		for ($y = 0; $y < $graphSize; $y++)
		// 		{
		// 			for ($x = 0; $x < $graphSize; $x++)
		// 			{
		// 				if (g[x, y] != g32[x, y])
		// 					throw new Exception();
		// 			}
		// 		}

		// 		bool b;
		// 		if ((b = g.HasUnevenSubgraph()) != g32.HasUnevenSubgraph())
		// 			throw new Exception();

		// 		Console.WriteLine(i + " " + b);
		// 	}

		// }

		// CHECK Potentieel risico
		public function CountEdges($node)
		{
			$i = 0;
			$bits = $this->data[$node] & ~(-1 << sizeof($this->data));

			for (; $bits != 0; $i++)
				$bits &= $bits - 1;

			return $i;
		}
	}
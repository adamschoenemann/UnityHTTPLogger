using UnityEngine;
using System.Collections;
using System.Collections.Generic;
using Logging;

public class GameObjectLoggable : Loggable
{

	protected override void BeforeEnqueueEntry(LogEntry entry)
	{
		entry.AddGameObject(gameObject.name, gameObject);
	}


}
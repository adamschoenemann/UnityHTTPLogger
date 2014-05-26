using UnityEngine;
using System.Collections;
using System.Collections.Generic;

public class GameObjectLoggable : Loggable
{

	public override bool ShouldLogRoutinely()
	{
		return true;
	}

	protected override void BeforeEnqueueEntry(LogEntry entry)
	{
		entry.AddGameObject(gameObject.name, gameObject);
	}


}
using UnityEngine;
using System.Collections;
using System.Collections.Generic;
using System;

// TODO: Get rid of looping logging
public class Loggable : MonoBehaviour
{

	public static float logRate = 0.5f;
	public bool enabled = true;
	public string name;
	public int id {get; private set;}
	protected Logger logger;

	protected void Awake()
	{
		if(enabled == false)
			return;
		SetupLogging();
	}

	protected virtual void SetupLogging()
	{
		this.id = 0;
		// logger = GameObject.FindWithTag(Tags.logger).GetComponent<Logger>();
		logger = Logger.instance;
		StartCoroutine(Register());
		if(String.IsNullOrEmpty(name))
		{
			name = gameObject.name;
		}
	}

	private IEnumerator Register()
	{
		while(logger == null || logger.scene_id == 0)
		{
			logger = Logger.instance;
			yield return new WaitForSeconds(0.5f);
		}

		logger.RegisterLoggable(this, id => this.id = id);
	}

	public virtual bool ShouldLogRoutinely()
	{
		return false;
	}

	public void EnqueueEntry(LogEntry entry)
	{
		if(enabled == false) return;
		if(id <= 0)
		{
			StartCoroutine(WaitForId(entry));
		}
		else
		{
			logger.Enqueue(entry);
		}
	}

	private IEnumerator WaitForId(LogEntry entry)
	{
		while(id <= 0)
		{
			Debug.Log("Waiting for id...");
			yield return new WaitForSeconds(1.0f);
			Debug.Log("Id found");
		}
		EnqueueEntry(entry);
	}

	protected void Start()
	{
		if(ShouldLogRoutinely())
			StartLoggingRoutine();
	}

	protected void StartLoggingRoutine()
	{
		// print("Log");
		StartCoroutine(LoggingRoutine());	
	}

	protected virtual void Log()
	{
		if(id == 0) return;
		LogEntry entry = new LogEntry(this);
		
		BeforeEnqueueEntry(entry);

		EnqueueEntry(entry);
	}

	protected virtual void BeforeEnqueueEntry(LogEntry entry)
	{

	}

	public IEnumerator LoggingRoutine()
	{
		while(true)
		{
			if(enabled)
				Log();
			yield return new WaitForSeconds(logRate);
		}
	}

	void Stop()
	{
		StopAllCoroutines();
	}

	void OnDestroy()
	{
		StopAllCoroutines();
	}



}
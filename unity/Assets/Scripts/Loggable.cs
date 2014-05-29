using UnityEngine;
using System.Collections;
using System.Collections.Generic;
using System;

namespace Logging
{

	public class Loggable : MonoBehaviour
	{

		public bool Active = true;
		public string name;
		public int id {get; private set;}
		protected Logger logger;

		protected void Awake()
		{
			if(Active == false)
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

		public void EnqueueEntry(LogEntry entry)
		{
			if(Active == false) return;
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

		void Stop()
		{
			StopAllCoroutines();
		}

		void OnDestroy()
		{
			StopAllCoroutines();
		}

	}
}
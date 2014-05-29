using UnityEngine;
using System;
using System.Collections;
using System.Collections.Generic;
using System.Text;

namespace Logging
{
	
	public class Logger : MonoBehaviour
	{

		public static Logger instance {get; private set;}
		public int scene_id { get; private set; }
		public bool Active = true;

		public bool IsActive()
		{
			return (instance != null && this == instance && Active);
		}

		void Awake()
		{
			if(Active == false) return;
			if(instance != null && instance != this)
			{
				Destroy(gameObject);
				CancelInvoke();
				Debug.LogError("Multiple Loggers found!!");
				return;
			}
			Debug.Log("Setting Logger instance");
			instance = this;
			GameObject.DontDestroyOnLoad(gameObject);
			SetupLogging();
		}

		private void SetupLogging()
		{
			StartCoroutine(LogAPI.instance.StartSession(this)); // Register session
			StartCoroutine(RegisterScene()); // register scene
		}

		void OnLevelWasLoaded()
		{
			if(IsActive() == false) return;
			Debug.Log("Logger " + gameObject.GetInstanceID() + ": OnLevelWasLoaded");
			StartCoroutine(RegisterScene());
		}

		void OnApplicationQuit()
		{
			if(IsActive() == false) return;
			if(LogAPI.instance.Enqueued() > 0)
			{
				Flush();
			}
			StartCoroutine(LogAPI.instance.CloseScene(scene_id, this));
			StartCoroutine(LogAPI.instance.StopSession(this));
		}

		private IEnumerator RegisterScene()
		{
			if(IsActive() == false) yield return false;
			while(LogAPI.instance.session_id == 0)
			{
				yield return new WaitForSeconds(0.5f);
			}
			if(scene_id != 0)
			{
				// Unregister current scene
				if(LogAPI.instance.Enqueued() > 0)
				{
					StartCoroutine(LogAPI.instance.Flush(this));
				}
				StartCoroutine(LogAPI.instance.CloseScene(scene_id, this));
			}
			StartCoroutine(LogAPI.instance.RegisterScene(
				Application.loadedLevelName,
				Time.time,
				this,
				id => {
					scene_id = id;
					Debug.Log("Scene registered with id: " + id);
				}
			));
		}


		public void RegisterLoggable(Loggable l, Action<int> cb)
		{
			if(IsActive() == false) return;
			StartCoroutine(LogAPI.instance.RegisterLoggable(l, this, cb));
		}

		public void Enqueue(LogEntry entry)
		{
			if(IsActive() == false) return;

			entry.scene_id = scene_id;
			entry.AddFloat("fps", 1.0f/Time.deltaTime);
			if(LogAPI.instance.Enqueue(entry))
			{
				Flush();
			}

		}

		public void Flush()
		{
			StartCoroutine(_Flush());
		}

		private IEnumerator _Flush()
		{
			while(scene_id <= 0)
			{
				yield return true;
			}
			StartCoroutine(LogAPI.instance.Flush(this));
		}

		public void SendPersonalityTest(string[] answers)
		{
			StartCoroutine(SendPersonalityAnswers(answers));
		}

		private IEnumerator SendPersonalityAnswers(string[] answers)
		{
			while(LogAPI.instance.session_id == 0 || scene_id == 0)
			{
				yield return new WaitForSeconds(0.5f);
			}

			WWWForm form = new WWWForm();
			form.AddField("session_id", LogAPI.instance.session_id);

			form.AddField("timestamp", answers[0]);
			form.AddField("gender", answers[1]);
			form.AddField("age", answers[2]);
			form.AddField("nationality", answers[3]);

			// Join answers
			StringBuilder sb = new StringBuilder();
			for(int i = 4; i < answers.Length - 1; i++)
			{
				sb.Append(answers[i]).Append("|");
			}
			sb.Append(answers[answers.Length - 1]);
			form.AddField("answers", sb.ToString());

			string url = "http://www.adamschoenemann.dk/api/answers";
			WWW www = new WWW(url, form);
			yield return www;
			if(String.IsNullOrEmpty(www.error) == false)
			{
				yield return StartCoroutine(Utils.RetryConnection(www, form));
			}
			if(String.IsNullOrEmpty(www.error))
			{
				Debug.Log("Questionnaire posted succesfully");
			}
			else
			{
				Debug.Log(www.error);
			}
		}

		public void SendPilotTest(string[] answers)
		{
			StartCoroutine(SendPilotTestAnswers(answers));
		}

		private IEnumerator SendPilotTestAnswers(string[] answers)
		{
			while(LogAPI.instance.session_id == 0 || scene_id == 0)
			{
				yield return new WaitForSeconds(0.5f);
			}

			WWWForm form = new WWWForm();
			form.AddField("session_id", LogAPI.instance.session_id);

			form.AddField("timestamp", answers[0]);
			form.AddField("gender", answers[1]);
			form.AddField("age", answers[2]);
			form.AddField("nationality", answers[3]);
			form.AddField("est_play_time", answers[4]);
			form.AddField("com_appearance", answers[5]);
			form.AddField("com_choices", answers[6]);
			form.AddField("com_other", answers[7]);

			// Join answers
			StringBuilder sb = new StringBuilder();
			for(int i = 8; i < answers.Length - 1; i++)
			{
				sb.Append(answers[i]).Append("|");
			}
			sb.Append(answers[answers.Length - 1]);
			form.AddField("answers", sb.ToString());

			string url = "http://www.adamschoenemann.dk/api/pilot_answers";
			WWW www = new WWW(url, form);
			yield return www;
			if(String.IsNullOrEmpty(www.error) == false)
			{
				yield return StartCoroutine(Utils.RetryConnection(www, form));
			}
			if(String.IsNullOrEmpty(www.error))
			{
				Debug.Log("Pilot answers posted succesfully");
			}
			else
			{
				Debug.Log(www.error);
			}
		}
	}
}